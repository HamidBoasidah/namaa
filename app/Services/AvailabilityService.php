<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService as ConsultantServiceModel;
use App\Repositories\BookingRepository;
use App\Repositories\ConsultantHolidayRepository;
use App\Repositories\ConsultantWorkingHourRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    protected BookingRepository $bookings;
    protected ConsultantHolidayRepository $holidays;
    protected ConsultantWorkingHourRepository $workingHours;

    public function __construct(
        BookingRepository $bookings,
        ConsultantHolidayRepository $holidays,
        ConsultantWorkingHourRepository $workingHours
    ) {
        $this->bookings = $bookings;
        $this->holidays = $holidays;
        $this->workingHours = $workingHours;
    }

    /**
     * Check if a date is a holiday for the consultant
     */
    public function isHoliday(int $consultantId, Carbon $date): bool
    {
        return $this->holidays->forConsultant($consultantId, [])
            ->whereDate('holiday_date', $date->toDateString())
            ->exists();
    }

    /**
     * Get active working hours for a consultant on a specific weekday
     * 
     * @param int $consultantId
     * @param int $dayOfWeek 0=Sunday, 1=Monday, ..., 6=Saturday
     * @return Collection
     */
    public function getWorkingHoursForDay(int $consultantId, int $dayOfWeek): Collection
    {
        return $this->workingHours->forConsultantDay($consultantId, $dayOfWeek, true, []);
    }

    /**
     * Check if a time range fits within any active working hour slot
     * 
     * @param int $consultantId
     * @param Carbon $startAt
     * @param Carbon $endAt End time including buffer
     * @return bool
     */
    public function fitsInWorkingHours(int $consultantId, Carbon $startAt, Carbon $endAt): bool
    {
        $dayOfWeek = (int) $startAt->dayOfWeek;
        $workingHours = $this->getWorkingHoursForDay($consultantId, $dayOfWeek);

        if ($workingHours->isEmpty()) {
            return false;
        }

        $startTime = $startAt->format('H:i');
        $endTime = $endAt->format('H:i');

        // Check if the booking fits within any working hour slot
        foreach ($workingHours as $wh) {
            if ($startTime >= $wh->start_time && $endTime <= $wh->end_time) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate if a specific slot is available
     * 
     * @param int $consultantId
     * @param Carbon $startAt
     * @param int $durationMinutes
     * @param int $bufferAfterMinutes
     * @param int|null $excludeBookingId Exclude this booking from conflict check
     * @return array ['valid' => bool, 'reason' => string|null]
     */
    public function validateSlot(
        int $consultantId,
        Carbon $startAt,
        int $durationMinutes,
        int $bufferAfterMinutes,
        ?int $excludeBookingId = null
    ): array {
        $endAt = $startAt->copy()->addMinutes($durationMinutes);
        $occupiedEnd = $endAt->copy()->addMinutes($bufferAfterMinutes);

        // Check holiday
        if ($this->isHoliday($consultantId, $startAt)) {
            return [
                'valid' => false,
                'reason' => 'holiday_conflict',
                'message' => 'المستشار في إجازة في هذا التاريخ',
            ];
        }

        // Check working hours (include buffer in end time check)
        if (!$this->fitsInWorkingHours($consultantId, $startAt, $occupiedEnd)) {
            return [
                'valid' => false,
                'reason' => 'outside_working_hours',
                'message' => 'الوقت المحدد خارج ساعات عمل المستشار',
            ];
        }

        // Check overlapping bookings
        $overlaps = $this->bookings->findBlockingOverlaps(
            $consultantId,
            $startAt,
            $occupiedEnd,
            $excludeBookingId
        );

        if ($overlaps->isNotEmpty()) {
            return [
                'valid' => false,
                'reason' => 'slot_unavailable',
                'message' => 'الموعد المحدد غير متاح',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
            'message' => null,
        ];
    }

    /**
     * Get available time slots for a consultant on a specific date
     * 
     * @param int $consultantId
     * @param Carbon $date
     * @param string|null $bookableType 'consultant' or 'consultant_service'
     * @param int|null $bookableId
     * @param int $granularity Time slot granularity in minutes (default 5)
     * @return array Array of available slot start times (ISO strings)
     */
    public function getAvailableSlots(
        int $consultantId,
        Carbon $date,
        ?string $bookableType = null,
        ?int $bookableId = null,
        int $granularity = 30
    ): array {
        // Check if date is a holiday
        if ($this->isHoliday($consultantId, $date)) {
            return [];
        }

        // Get duration and buffer based on bookable
        [$durationMinutes, $bufferMinutes] = $this->resolveDurationAndBuffer(
            $consultantId,
            $bookableType,
            $bookableId
        );

        // Get working hours for the day
        $dayOfWeek = (int) $date->dayOfWeek;
        $workingHours = $this->getWorkingHoursForDay($consultantId, $dayOfWeek);

        if ($workingHours->isEmpty()) {
            return [];
        }

        // Get blocking bookings for the date
        $blockingBookings = $this->bookings->getBlockingForDate($consultantId, $date);

        // Generate available slots
        $availableSlots = [];

        foreach ($workingHours as $wh) {
            $slotStart = $date->copy()->setTimeFromTimeString($wh->start_time);
            $workEnd = $date->copy()->setTimeFromTimeString($wh->end_time);

            // Generate slots within this working hour period
            while ($slotStart->copy()->addMinutes($durationMinutes + $bufferMinutes)->lte($workEnd)) {
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);
                $occupiedEnd = $slotEnd->copy()->addMinutes($bufferMinutes);

                // Check if slot overlaps with any blocking booking
                $isBlocked = false;
                foreach ($blockingBookings as $booking) {
                    $bookingOccupiedEnd = $booking->end_at->copy()->addMinutes($booking->buffer_after_minutes);
                    
                    // Overlap: slot_start < booking_occupied_end AND slot_occupied_end > booking_start
                    if ($slotStart->lt($bookingOccupiedEnd) && $occupiedEnd->gt($booking->start_at)) {
                        $isBlocked = true;
                        break;
                    }
                }

                // Skip past slots (if date is today)
                if ($slotStart->lt(now())) {
                    $slotStart->addMinutes($granularity);
                    continue;
                }

                if (!$isBlocked) {
                    $availableSlots[] = $slotStart->format('H:i');
                }

                $slotStart->addMinutes($granularity);
            }
        }

        return $availableSlots;
    }

    /**
     * Resolve duration and buffer based on bookable type
     * 
     * @return array [durationMinutes, bufferMinutes]
     */
    protected function resolveDurationAndBuffer(
        int $consultantId,
        ?string $bookableType,
        ?int $bookableId
    ): array {
        $consultant = Consultant::find($consultantId);
        $defaultDuration = 60; // Default 1 hour
        $defaultBuffer = $consultant?->buffer ?? 0;

        if ($bookableType === 'consultant_service' && $bookableId) {
            $service = ConsultantServiceModel::find($bookableId);
            if ($service) {
                $duration = $service->duration_minutes ?? $defaultDuration;
                $buffer = $service->buffer ?? $defaultBuffer;
                return [$duration, $buffer];
            }
        }

        // For direct consultant booking or fallback
        return [$defaultDuration, $defaultBuffer];
    }
}
