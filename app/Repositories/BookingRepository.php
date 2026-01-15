<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Eloquent\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BookingRepository extends BaseRepository
{
    protected array $defaultWith = [
        'client:id,first_name,last_name,avatar,email',
        'consultant.user:id,first_name,last_name,avatar,email',
    ];

    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a pending booking
     * Should be called within a transaction with consultant lock
     */
    public function createPending(array $data): Booking
    {
        $data['status'] = Booking::STATUS_PENDING;
        $data['expires_at'] = now()->addMinutes(Booking::PENDING_HOLD_MINUTES);

        /** @var Booking $booking */
        $booking = $this->create($data);
        
        return $booking;
    }

    /**
     * Find blocking bookings that overlap with given time range
     * 
     * Blocking = confirmed OR (pending AND expires_at > now)
     * Overlap considers buffer_after_minutes in occupied window
     * 
     * @param int $consultantId
     * @param Carbon $occupiedStart Start of new booking
     * @param Carbon $occupiedEnd End of new booking + buffer
     * @param int|null $excludeBookingId Exclude this booking from check (for confirmation re-check)
     * @return Collection
     */
    public function findBlockingOverlaps(
        int $consultantId,
        Carbon $occupiedStart,
        Carbon $occupiedEnd,
        ?int $excludeBookingId = null
    ): Collection {
        $query = $this->model->newQuery()
            ->forConsultant($consultantId)
            ->blocking()
            ->where(function (Builder $q) use ($occupiedStart, $occupiedEnd) {
                // Overlap condition:
                // new_start < existing_occupied_end AND new_occupied_end > existing_start
                // existing_occupied_end = end_at + buffer_after_minutes
                // Use database-agnostic approach: datetime() for SQLite, DATE_ADD for MySQL
                $driver = $q->getConnection()->getDriverName();
                
                if ($driver === 'sqlite') {
                    // SQLite: datetime(end_at, '+' || buffer_after_minutes || ' minutes')
                    $q->whereRaw("? < datetime(end_at, '+' || buffer_after_minutes || ' minutes')", [$occupiedStart->toDateTimeString()])
                      ->whereRaw('? > start_at', [$occupiedEnd->toDateTimeString()]);
                } else {
                    // MySQL: DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)
                    $q->whereRaw('? < DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)', [$occupiedStart])
                      ->whereRaw('? > start_at', [$occupiedEnd]);
                }
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get();
    }

    /**
     * Find blocking bookings that overlap with given time range WITH PESSIMISTIC LOCK
     * 
     * This method acquires a FOR UPDATE lock on the conflicting bookings to prevent
     * race conditions when multiple requests try to book the same time slot.
     * 
     * MUST be called within a database transaction.
     * 
     * Blocking = confirmed OR (pending AND expires_at > now)
     * Overlap considers buffer_after_minutes in occupied window
     * 
     * @param int $consultantId
     * @param Carbon $occupiedStart Start of new booking
     * @param Carbon $occupiedEnd End of new booking + buffer
     * @param int|null $excludeBookingId Exclude this booking from check (for confirmation re-check)
     * @return Collection Locked collection of conflicting bookings
     */
    public function findBlockingOverlapsWithLock(
        int $consultantId,
        Carbon $occupiedStart,
        Carbon $occupiedEnd,
        ?int $excludeBookingId = null
    ): Collection {
        $query = $this->model->newQuery()
            ->forConsultant($consultantId)
            ->blocking()
            ->where(function (Builder $q) use ($occupiedStart, $occupiedEnd) {
                // Overlap condition:
                // new_start < existing_occupied_end AND new_occupied_end > existing_start
                // existing_occupied_end = end_at + buffer_after_minutes
                $driver = $q->getConnection()->getDriverName();
                
                if ($driver === 'sqlite') {
                    // SQLite doesn't support FOR UPDATE, but we still need the query
                    // The consultant lock will provide serialization for SQLite
                    $q->whereRaw("? < datetime(end_at, '+' || buffer_after_minutes || ' minutes')", [$occupiedStart->toDateTimeString()])
                      ->whereRaw('? > start_at', [$occupiedEnd->toDateTimeString()]);
                } else {
                    // MySQL: DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)
                    $q->whereRaw('? < DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)', [$occupiedStart])
                      ->whereRaw('? > start_at', [$occupiedEnd]);
                }
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        // Apply pessimistic lock - this will block other transactions
        // from reading/modifying these rows until current transaction commits
        return $query->lockForUpdate()->get();
    }

    /**
     * Get all blocking bookings for a consultant on a specific date
     */
    public function getBlockingForDate(int $consultantId, Carbon $date): Collection
    {
        return $this->model->newQuery()
            ->forConsultant($consultantId)
            ->blocking()
            ->onDate($date)
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Confirm a pending booking
     */
    public function confirm(Booking $booking): Booking
    {
        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => null,
        ]);

        return $booking->fresh();
    }

    /**
     * Cancel a booking with canceller info
     */
    public function cancel(Booking $booking, Model $cancelledBy, ?string $reason = null): Booking
    {
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_type' => get_class($cancelledBy),
            'cancelled_by_id' => $cancelledBy->id,
            'cancel_reason' => $reason,
        ]);

        return $booking->fresh();
    }

    /**
     * Expire all pending bookings where expires_at <= now
     * Returns count of expired bookings
     */
    public function expirePending(): int
    {
        return $this->model->newQuery()
            ->expiredPending()
            ->update(['status' => Booking::STATUS_EXPIRED]);
    }

    /**
     * Get bookings for a client
     */
    public function forClient(int $clientId, ?array $with = null): Builder
    {
        return $this->makeQuery($with)->forClient($clientId);
    }

    /**
     * Get bookings for a consultant
     */
    public function forConsultant(int $consultantId, ?array $with = null): Builder
    {
        return $this->makeQuery($with)->forConsultant($consultantId);
    }

    /**
     * Find booking by ID with relationships
     */
    public function findWithRelations(int $id): ?Booking
    {
        return $this->model->newQuery()
            ->with([
                'client:id,first_name,last_name,avatar',
                'consultant.user:id,first_name,last_name,avatar',
                'bookable',
                'cancelledBy',
            ])
            ->find($id);
    }
}
