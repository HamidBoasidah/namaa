<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantHoliday;
use App\Models\ConsultantService;
use App\Models\ConsultantWorkingHour;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for AvailabilityService
 * 
 * @property Feature: bookings-backend, Property 4: Working Hours Validation
 * @property Feature: bookings-backend, Property 5: Holiday Validation
 * @property Feature: bookings-backend, Property 11: Available Slots Calculation
 * @validates Requirements 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 12.2, 12.3, 12.4, 12.5, 12.6
 */
class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $service;
    protected Consultant $consultant;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(AvailabilityService::class);
        $this->consultant = Consultant::factory()->create(['buffer' => 15]);
        $this->client = User::factory()->create();
    }

    /**
     * Helper to create working hours
     */
    protected function createWorkingHours(int $dayOfWeek, string $start, string $end): ConsultantWorkingHour
    {
        return ConsultantWorkingHour::create([
            'consultant_id' => $this->consultant->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $start,
            'end_time' => $end,
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a holiday
     */
    protected function createHoliday(string $date): ConsultantHoliday
    {
        return ConsultantHoliday::create([
            'consultant_id' => $this->consultant->id,
            'holiday_date' => $date,
            'name' => 'Test Holiday',
        ]);
    }

    /**
     * Helper to create a booking
     */
    protected function createBooking(Carbon $start, Carbon $end, string $status = Booking::STATUS_CONFIRMED): Booking
    {
        return Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => $start,
            'end_at' => $end,
            'duration_minutes' => $start->diffInMinutes($end),
            'buffer_after_minutes' => 15,
            'status' => $status,
            'expires_at' => $status === Booking::STATUS_PENDING ? now()->addMinutes(15) : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Working Hours Validation
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Booking within working hours is valid
     */
    public function test_booking_within_working_hours_is_valid(): void
    {
        // Monday (1) working hours: 09:00 - 17:00
        $this->createWorkingHours(1, '09:00', '17:00');

        // Booking 10:00 - 11:00 with 15 min buffer = ends at 11:15
        $startAt = Carbon::parse('2026-01-19 10:00:00'); // Monday
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60, // duration
            15  // buffer
        );

        $this->assertTrue($result['valid']);
    }

    /**
     * Property: Booking before working hours start is invalid
     */
    public function test_booking_before_working_hours_is_invalid(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');

        // Booking 08:00 - 09:00 (before working hours)
        $startAt = Carbon::parse('2026-01-19 08:00:00'); // Monday
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60,
            15
        );

        $this->assertFalse($result['valid']);
        $this->assertEquals('outside_working_hours', $result['reason']);
    }

    /**
     * Property: Booking that exceeds working hours end (with buffer) is invalid
     */
    public function test_booking_exceeding_working_hours_with_buffer_is_invalid(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');

        // Booking 16:00 - 17:00 with 15 min buffer = ends at 17:15 (exceeds 17:00)
        $startAt = Carbon::parse('2026-01-19 16:00:00'); // Monday
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60,
            15
        );

        $this->assertFalse($result['valid']);
        $this->assertEquals('outside_working_hours', $result['reason']);
    }

    /**
     * Property: Booking on day with no working hours is invalid
     */
    public function test_booking_on_day_without_working_hours_is_invalid(): void
    {
        // Only Monday has working hours
        $this->createWorkingHours(1, '09:00', '17:00');

        // Try to book on Tuesday (no working hours)
        $startAt = Carbon::parse('2026-01-20 10:00:00'); // Tuesday
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60,
            15
        );

        $this->assertFalse($result['valid']);
        $this->assertEquals('outside_working_hours', $result['reason']);
    }

    /**
     * Property: Booking within any of multiple working hour slots is valid
     */
    public function test_booking_within_any_working_hour_slot_is_valid(): void
    {
        // Monday has two slots: 09:00-12:00 and 14:00-18:00
        $this->createWorkingHours(1, '09:00', '12:00');
        $this->createWorkingHours(1, '14:00', '18:00');

        // Booking in afternoon slot: 15:00 - 16:00
        $startAt = Carbon::parse('2026-01-19 15:00:00'); // Monday
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60,
            15
        );

        $this->assertTrue($result['valid']);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Holiday Validation
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Booking on holiday date is invalid
     */
    public function test_booking_on_holiday_is_invalid(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');
        $this->createHoliday('2026-01-19'); // Monday

        $startAt = Carbon::parse('2026-01-19 10:00:00');
        $result = $this->service->validateSlot(
            $this->consultant->id,
            $startAt,
            60,
            15
        );

        $this->assertFalse($result['valid']);
        $this->assertEquals('holiday_conflict', $result['reason']);
    }

    /**
     * Property: Holiday check is by date only (any time on holiday is blocked)
     */
    public function test_holiday_blocks_entire_day(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');
        $this->createHoliday('2026-01-19');

        // Try different times on the same holiday
        $times = ['09:00:00', '12:00:00', '16:00:00'];
        
        foreach ($times as $time) {
            $startAt = Carbon::parse("2026-01-19 {$time}");
            $result = $this->service->validateSlot(
                $this->consultant->id,
                $startAt,
                60,
                15
            );

            $this->assertFalse($result['valid']);
            $this->assertEquals('holiday_conflict', $result['reason']);
        }
    }

    /**
     * Property: isHoliday returns true for holiday dates
     */
    public function test_is_holiday_returns_true_for_holidays(): void
    {
        $this->createHoliday('2026-01-19');

        $this->assertTrue($this->service->isHoliday(
            $this->consultant->id,
            Carbon::parse('2026-01-19')
        ));

        $this->assertFalse($this->service->isHoliday(
            $this->consultant->id,
            Carbon::parse('2026-01-20')
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // Property 11: Available Slots Calculation
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Holiday date returns empty slots
     */
    public function test_available_slots_empty_on_holiday(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');
        $this->createHoliday('2026-01-19');

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19')
        );

        $this->assertEmpty($slots);
    }

    /**
     * Property: Day without working hours returns empty slots
     */
    public function test_available_slots_empty_without_working_hours(): void
    {
        // No working hours created

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19')
        );

        $this->assertEmpty($slots);
    }

    /**
     * Property: All returned slots are within working hours
     */
    public function test_all_slots_within_working_hours(): void
    {
        $this->createWorkingHours(1, '09:00', '12:00');

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19'),
            'consultant',
            $this->consultant->id
        );

        foreach ($slots as $slotIso) {
            $slotTime = Carbon::parse($slotIso);
            $this->assertGreaterThanOrEqual('09:00', $slotTime->format('H:i'));
            // With 60 min duration + 15 min buffer, last slot should start by 10:45
            $this->assertLessThanOrEqual('10:45', $slotTime->format('H:i'));
        }
    }

    /**
     * Property: All returned slot times are multiples of 5 minutes
     */
    public function test_all_slots_are_5_minute_granularity(): void
    {
        $this->createWorkingHours(1, '09:00', '12:00');

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19')
        );

        foreach ($slots as $slotIso) {
            $slotTime = Carbon::parse($slotIso);
            $this->assertEquals(0, $slotTime->minute % 5, "Slot {$slotIso} is not 5-minute aligned");
        }
    }

    /**
     * Property: Slots overlapping with blocking bookings are excluded
     */
    public function test_slots_overlapping_bookings_excluded(): void
    {
        $this->createWorkingHours(1, '09:00', '17:00');

        // Create a booking from 10:00 to 11:00 with 15 min buffer
        // Occupied window: 10:00 - 11:15
        $this->createBooking(
            Carbon::parse('2026-01-19 10:00:00'),
            Carbon::parse('2026-01-19 11:00:00')
        );

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19')
        );

        // Slots from 10:00 to 11:10 should be excluded (they would overlap)
        foreach ($slots as $slotIso) {
            $slotTime = Carbon::parse($slotIso);
            $slotTimeStr = $slotTime->format('H:i');
            
            // With 60 min duration + 15 min buffer:
            // - Slot at 10:00 would occupy 10:00-11:15 (overlaps with 10:00-11:15)
            // - Slot at 09:00 would occupy 09:00-10:15 (overlaps with 10:00-11:15)
            // First available after booking: 11:15
            if ($slotTimeStr >= '09:00' && $slotTimeStr < '11:15') {
                // These slots should not exist if they would overlap
                $slotEnd = $slotTime->copy()->addMinutes(60 + 15);
                $bookingStart = Carbon::parse('2026-01-19 10:00:00');
                $bookingOccupiedEnd = Carbon::parse('2026-01-19 11:15:00');
                
                // Verify no overlap
                $overlaps = $slotTime->lt($bookingOccupiedEnd) && $slotEnd->gt($bookingStart);
                $this->assertFalse($overlaps, "Slot at {$slotTimeStr} should not be available");
            }
        }
    }

    /**
     * Property: Slots consider duration and buffer of bookable
     */
    public function test_slots_consider_service_duration_and_buffer(): void
    {
        $this->createWorkingHours(1, '09:00', '12:00');

        // Create a service with 30 min duration and 10 min buffer
        $service = ConsultantService::factory()->create([
            'consultant_id' => $this->consultant->id,
            'duration_minutes' => 30,
            'buffer' => 10,
        ]);

        $slots = $this->service->getAvailableSlots(
            $this->consultant->id,
            Carbon::parse('2026-01-19'),
            'consultant_service',
            $service->id
        );

        // With 30 min duration + 10 min buffer = 40 min total
        // Working hours 09:00-12:00 = 180 min
        // Last slot should be at 11:20 (11:20 + 40 = 12:00)
        $this->assertNotEmpty($slots);
        
        $lastSlot = Carbon::parse(end($slots));
        $this->assertLessThanOrEqual('11:20', $lastSlot->format('H:i'));
    }
}
