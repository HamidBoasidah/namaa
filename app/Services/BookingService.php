<?php

namespace App\Services;

use App\DTOs\CreatePendingBookingDTO;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService as ConsultantServiceModel;
use App\Repositories\BookingRepository;
use App\Repositories\ConsultantRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    protected BookingRepository $bookings;
    protected AvailabilityService $availability;
    protected ConsultantRepository $consultants;

    public function __construct(
        BookingRepository $bookings,
        AvailabilityService $availability,
        ConsultantRepository $consultants
    ) {
        $this->bookings = $bookings;
        $this->availability = $availability;
        $this->consultants = $consultants;
    }

    /**
     * Create a pending booking with slot hold
     * Uses transaction + pessimistic locking to prevent race conditions
     * 
     * The locking strategy:
     * 1. Lock consultant record (serializes all bookings for this consultant)
     * 2. Lock any potentially conflicting bookings (prevents phantom reads)
     * 3. Validate working hours and holidays
     * 4. Check for conflicts using locked data
     * 5. Create booking
     * 
     * @throws ValidationException if slot unavailable or validation fails
     */
    public function createPending(CreatePendingBookingDTO $dto): Booking
    {
        // Validate time granularity (5-minute rule)
        $this->validateTimeGranularity($dto->start_at, $dto->duration_minutes);

        // Resolve bookable model class
        $bookableClass = $this->resolveBookableClass($dto->bookable_type);
        
        return DB::transaction(function () use ($dto, $bookableClass) {
            // Step 1: Lock consultant record to serialize all booking attempts
            $consultant = Consultant::lockForUpdate()->findOrFail($dto->consultant_id);

            // Validate bookable exists and belongs to consultant
            $bookable = $this->validateAndGetBookable(
                $bookableClass,
                $dto->bookable_id,
                $consultant->id
            );

            // Resolve duration and buffer
            [$durationMinutes, $bufferMinutes] = $this->resolveDurationAndBuffer(
                $dto->bookable_type,
                $bookable,
                $dto->duration_minutes,
                $consultant
            );

            // Snapshot price at booking creation
            $price = $this->calculatePrice(
                $dto->bookable_type,
                $bookable,
                $durationMinutes,
                $consultant
            );

            // Resolve consultation method
            $consultationMethod = $this->resolveConsultationMethod(
                $dto->bookable_type,
                $bookable,
                $dto->consultation_method
            );

            $startAt = Carbon::parse($dto->start_at);
            $endAt = $startAt->copy()->addMinutes($durationMinutes);
            $occupiedEnd = $endAt->copy()->addMinutes($bufferMinutes);

            // Step 2: Check working hours and holidays (no locking needed)
            if ($this->availability->isHoliday($consultant->id, $startAt)) {
                throw ValidationException::withMessages([
                    'start_at' => ['المستشار في إجازة في هذا التاريخ'],
                ]);
            }

            if (!$this->availability->fitsInWorkingHours($consultant->id, $startAt, $occupiedEnd)) {
                throw ValidationException::withMessages([
                    'start_at' => ['الوقت المحدد خارج ساعات عمل المستشار'],
                ]);
            }

            // Step 3: Lock and check for conflicting bookings
            // This is the critical section - we lock all potentially conflicting bookings
            // to prevent race conditions where two requests pass the check simultaneously
            $conflicts = $this->bookings->findBlockingOverlapsWithLock(
                $consultant->id,
                $startAt,
                $occupiedEnd
            );

            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'start_at' => ['الموعد المحدد غير متاح - يوجد حجز آخر في هذا الوقت'],
                ]);
            }

            // Step 4: Create pending booking (safe now - we have exclusive access)
            return $this->bookings->createPending([
                'client_id' => $dto->client_id,
                'consultant_id' => $consultant->id,
                'bookable_type' => $bookableClass,
                'bookable_id' => $dto->bookable_id,
                'price' => $price,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'duration_minutes' => $durationMinutes,
                'buffer_after_minutes' => $bufferMinutes,
                'consultation_method' => $consultationMethod,
                'notes' => $dto->notes,
            ]);
        });
    }

    /**
     * Confirm a pending booking
     * Uses transaction + pessimistic locking
     * Re-checks availability before confirming
     * 
     * @throws ValidationException if booking expired or conflicts detected
     */
    public function confirm(int $bookingId, int $clientId): Booking
    {
        return DB::transaction(function () use ($bookingId, $clientId) {
            // Find booking
            $booking = $this->bookings->findOrFail($bookingId);

            // Verify ownership
            if ($booking->client_id !== $clientId) {
                throw ValidationException::withMessages([
                    'booking' => ['غير مصرح لك بتأكيد هذا الحجز'],
                ]);
            }

            // Verify status is pending
            if ($booking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'booking' => ['لا يمكن تأكيد هذا الحجز - الحالة غير صالحة'],
                ]);
            }

            // Verify not expired
            if (!$booking->expires_at || $booking->expires_at->lte(now())) {
                throw ValidationException::withMessages([
                    'booking' => ['انتهت صلاحية الحجز'],
                ]);
            }

            // Lock consultant to serialize confirmation attempts
            Consultant::lockForUpdate()->findOrFail($booking->consultant_id);

            // Calculate occupied end time
            $occupiedEnd = $booking->end_at->copy()->addMinutes($booking->buffer_after_minutes);

            // Re-check for conflicts with lock (exclude this booking from check)
            $conflicts = $this->bookings->findBlockingOverlapsWithLock(
                $booking->consultant_id,
                $booking->start_at,
                $occupiedEnd,
                $booking->id // Exclude self from conflict check
            );

            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'booking' => ['الموعد المحدد غير متاح - يوجد حجز آخر في هذا الوقت'],
                ]);
            }

            // Confirm booking
            return $this->bookings->confirm($booking);
        });
    }

    /**
     * Accept/confirm a pending booking by the consultant who owns the booking.
     * Similar to confirm() but verifies consultant ownership instead of client.
     *
     * @param int $bookingId
     * @param int $consultantId
     * @return Booking
     */
    public function acceptByConsultant(int $bookingId, int $consultantId): Booking
    {
        return DB::transaction(function () use ($bookingId, $consultantId) {
            // Find booking
            $booking = $this->bookings->findOrFail($bookingId);

            // Verify consultant ownership
            if ($booking->consultant_id !== $consultantId) {
                throw ValidationException::withMessages([
                    'booking' => ['غير مصرح لك بتأكيد هذا الحجز'],
                ]);
            }

            // Verify status is pending
            if ($booking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'booking' => ['لا يمكن تأكيد هذا الحجز - الحالة غير صالحة'],
                ]);
            }

            // Verify not expired
            if (!$booking->expires_at || $booking->expires_at->lte(now())) {
                throw ValidationException::withMessages([
                    'booking' => ['انتهت صلاحية الحجز'],
                ]);
            }

            // Lock consultant to serialize confirmation attempts
            Consultant::lockForUpdate()->findOrFail($booking->consultant_id);

            // Calculate occupied end time
            $occupiedEnd = $booking->end_at->copy()->addMinutes($booking->buffer_after_minutes);

            // Re-check for conflicts with lock (exclude this booking from check)
            $conflicts = $this->bookings->findBlockingOverlapsWithLock(
                $booking->consultant_id,
                $booking->start_at,
                $occupiedEnd,
                $booking->id // Exclude self
            );

            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'booking' => ['الموعد المحدد غير متاح - يوجد حجز آخر في هذا الوقت'],
                ]);
            }

            // Confirm booking
            return $this->bookings->confirm($booking);
        });
    }

    /**
     * Cancel a booking
     * Records who cancelled (polymorphic)
     */
    public function cancel(int $bookingId, Model $cancelledBy, ?string $reason = null): Booking
    {
        $booking = $this->bookings->findOrFail($bookingId);

        // Verify booking can be cancelled
        if (!$booking->canBeCancelled()) {
            throw ValidationException::withMessages([
                'booking' => ['لا يمكن إلغاء هذا الحجز'],
            ]);
        }

        return $this->bookings->cancel($booking, $cancelledBy, $reason);
    }

    /**
     * Expire all old pending bookings
     * Called by scheduled job
     * 
     * @return int Number of expired bookings
     */
    public function expireOldPending(): int
    {
        return $this->bookings->expirePending();
    }

    /**
     * Find booking by ID with relationships
     */
    public function find(int $id): ?Booking
    {
        return $this->bookings->findWithRelations($id);
    }

    /**
     * Paginate bookings with optional filters
     */
    public function paginate(int $perPage = 10, array $filters = [])
    {
        $query = $this->bookings->query([
            'client:id,first_name,last_name,email,avatar',
            'consultant.user:id,first_name,last_name,email,avatar',
            'bookable',
        ]);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by consultant
        if (!empty($filters['consultant_id'])) {
            $query->where('consultant_id', $filters['consultant_id']);
        }

        // Filter by client
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        // Search by client name or consultant name
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('consultant.user', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        return $query->latest('start_at')->paginate($perPage);
    }

    /**
     * Update a booking with conflict validation
     */
    public function update(int $id, array $data): Booking
    {
        return DB::transaction(function () use ($id, $data) {
            $booking = $this->bookings->findOrFail($id);
            
            // If time-related fields are being updated, check for conflicts
            if (isset($data['start_at']) || isset($data['end_at'])) {
                // Lock consultant to prevent race conditions
                Consultant::lockForUpdate()->findOrFail($booking->consultant_id);

                $startAt = isset($data['start_at']) ? Carbon::parse($data['start_at']) : $booking->start_at;
                $endAt = isset($data['end_at']) ? Carbon::parse($data['end_at']) : $booking->end_at;
                $bufferMinutes = $data['buffer_after_minutes'] ?? $booking->buffer_after_minutes ?? 0;

                // Check for conflicting bookings (excluding current booking)
                $conflictingBooking = Booking::where('consultant_id', $booking->consultant_id)
                    ->where('id', '!=', $id)
                    ->blocking()
                    ->where(function ($query) use ($startAt, $endAt, $bufferMinutes) {
                        $newOccupiedEnd = $endAt->copy()->addMinutes($bufferMinutes);
                        
                        $query->whereRaw('? < DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)', [$startAt])
                              ->whereRaw('? > start_at', [$newOccupiedEnd]);
                    })
                    ->first();

                if ($conflictingBooking) {
                    throw ValidationException::withMessages([
                        'start_at' => ['يوجد حجز آخر في هذا الوقت. الحجز المتعارض: #' . $conflictingBooking->id],
                    ]);
                }
            }

            // Recalculate price if duration or bookable context is available
            if ($booking->relationLoaded('bookable') === false) {
                $booking->load('bookable', 'consultant');
            }

            if ($booking->bookable && $booking->consultant) {
                $bookableType = $this->normalizeBookableType($booking->bookable_type);
                $durationMinutes = $data['duration_minutes'] ?? $booking->duration_minutes;
                $data['price'] = $this->calculatePrice(
                    $bookableType,
                    $booking->bookable,
                    $durationMinutes,
                    $booking->consultant
                );
            }

            return $this->bookings->update($id, $data);
        });
    }

    /**
     * Delete a booking
     */
    public function delete(int $id): bool
    {
        return $this->bookings->delete($id);
    }

    /**
     * Create a booking directly (admin) with conflict validation
     * Uses pessimistic locking to prevent race conditions
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Lock consultant to prevent race conditions
            $consultant = Consultant::lockForUpdate()->findOrFail($data['consultant_id']);

            // Normalize bookable type and validate
            $bookableType = $this->normalizeBookableType($data['bookable_type']);
            $bookableClass = $this->resolveBookableClass($bookableType);

            $bookable = $this->validateAndGetBookable(
                $bookableClass,
                $data['bookable_id'],
                $consultant->id
            );

            $startAt = Carbon::parse($data['start_at']);
            $endAt = Carbon::parse($data['end_at']);
            $bufferMinutes = $data['buffer_after_minutes'] ?? 0;
            $occupiedEnd = $endAt->copy()->addMinutes($bufferMinutes);

            // Ensure duration_minutes is set (admin forms always provide it)
            $durationMinutes = $data['duration_minutes'];

            // Snapshot price if not provided
            $data['price'] = $data['price'] ?? $this->calculatePrice(
                $bookableType,
                $bookable,
                $durationMinutes,
                $consultant
            );

            // Check for conflicting bookings with lock
            $conflicts = $this->bookings->findBlockingOverlapsWithLock(
                $consultant->id,
                $startAt,
                $occupiedEnd
            );

            if ($conflicts->isNotEmpty()) {
                $conflictingBooking = $conflicts->first();
                throw ValidationException::withMessages([
                    'start_at' => ['يوجد حجز آخر في هذا الوقت. الحجز المتعارض: #' . $conflictingBooking->id . ' من ' . $conflictingBooking->start_at->format('H:i') . ' إلى ' . $conflictingBooking->end_at->format('H:i')],
                ]);
            }

            return $this->bookings->create($data);
        });
    }

    /**
     * Validate time granularity (5-minute rule)
     */
    protected function validateTimeGranularity(string $startAt, ?int $durationMinutes): void
    {
        $start = Carbon::parse($startAt);
        
        // Check start time is multiple of 5 minutes
        if ($start->minute % 5 !== 0) {
            throw ValidationException::withMessages([
                'start_at' => ['وقت البداية يجب أن يكون من مضاعفات 5 دقائق'],
            ]);
        }

        // Check duration is multiple of 5 minutes (if provided)
        if ($durationMinutes !== null && $durationMinutes % 5 !== 0) {
            throw ValidationException::withMessages([
                'duration_minutes' => ['المدة يجب أن تكون من مضاعفات 5 دقائق'],
            ]);
        }
    }

    /**
     * Resolve bookable model class from type string
     */
    protected function resolveBookableClass(string $bookableType): string
    {
        return match ($bookableType) {
            'consultant' => Consultant::class,
            'consultant_service' => ConsultantServiceModel::class,
            default => throw ValidationException::withMessages([
                'bookable_type' => ['نوع الحجز غير صالح'],
            ]),
        };
    }

    /**
     * Validate bookable exists and belongs to consultant
     */
    protected function validateAndGetBookable(string $bookableClass, int $bookableId, int $consultantId): Model
    {
        $bookable = $bookableClass::find($bookableId);

        if (!$bookable) {
            throw ValidationException::withMessages([
                'bookable_id' => ['الخدمة أو المستشار غير موجود'],
            ]);
        }

        // For ConsultantService, verify it belongs to the consultant
        if ($bookableClass === ConsultantServiceModel::class) {
            if ($bookable->consultant_id !== $consultantId) {
                throw ValidationException::withMessages([
                    'bookable_id' => ['الخدمة لا تنتمي لهذا المستشار'],
                ]);
            }
        }

        // For Consultant, verify IDs match
        if ($bookableClass === Consultant::class) {
            if ($bookable->id !== $consultantId) {
                throw ValidationException::withMessages([
                    'bookable_id' => ['معرف المستشار غير متطابق'],
                ]);
            }
        }

        return $bookable;
    }

    /**
     * Resolve duration and buffer based on bookable type
     * 
     * @return array [durationMinutes, bufferMinutes]
     */
    protected function resolveDurationAndBuffer(
        string $bookableType,
        Model $bookable,
        ?int $userDuration,
        Consultant $consultant
    ): array {
        if ($bookableType === 'consultant_service') {
            // For service: use service duration, service buffer (or consultant buffer)
            $duration = $bookable->duration_minutes;
            $buffer = $bookable->buffer ?? $consultant->buffer ?? 0;
        } else {
            // For direct consultant booking: use user-provided duration, consultant buffer
            if ($userDuration === null) {
                throw ValidationException::withMessages([
                    'duration_minutes' => ['المدة مطلوبة للحجز المباشر مع المستشار'],
                ]);
            }
            $duration = $userDuration;
            $buffer = $consultant->buffer ?? 0;
        }

        return [$duration, $buffer];
    }

    /**
     * Calculate booking price based on bookable type
     * - Consultant service: fixed service price
     * - Direct consultant: hourly rate * duration (minutes / 60)
     */
    protected function calculatePrice(
        string $bookableType,
        Model $bookable,
        int $durationMinutes,
        Consultant $consultant
    ): float {
        if ($bookableType === 'consultant_service') {
            return round((float) ($bookable->price ?? 0), 2);
        }

        $hourlyRate = (float) ($consultant->price_per_hour ?? 0);
        $hours = $durationMinutes / 60;

        return round($hourlyRate * $hours, 2);
    }

    /**
     * Resolve consultation method based on bookable type
     * For service bookings: use service's consultation_method
     * For direct consultant bookings: use user-provided consultation_method
     */
    protected function resolveConsultationMethod(
        string $bookableType,
        Model $bookable,
        ?string $userMethod
    ): string {
        if ($bookableType === 'consultant_service') {
            // For service: use service's consultation method
            return $bookable->consultation_method;
        } else {
            // For direct consultant booking: use user-provided method
            if ($userMethod === null) {
                throw ValidationException::withMessages([
                    'consultation_method' => ['طريقة الاستشارة مطلوبة للحجز المباشر مع المستشار'],
                ]);
            }
            return $userMethod;
        }
    }

    /**
     * Normalize bookable type to slug form
     */
    protected function normalizeBookableType(string $bookableType): string
    {
        return match ($bookableType) {
            'consultant', Consultant::class => 'consultant',
            'consultant_service', ConsultantServiceModel::class => 'consultant_service',
            default => throw ValidationException::withMessages([
                'bookable_type' => ['نوع الحجز غير صالح'],
            ]),
        };
    }
}
