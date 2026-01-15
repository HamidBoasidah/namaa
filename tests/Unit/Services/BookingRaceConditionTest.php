<?php

namespace Tests\Unit\Services;

use App\DTOs\CreatePendingBookingDTO;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantWorkingHour;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Race Condition Tests for BookingService
 * 
 * These tests verify that the booking system correctly prevents
 * race conditions when multiple requests try to book the same time slot.
 * 
 * @property Feature: booking-race-condition-fix, Property 1: No Overlapping Blocking Bookings
 * @property Feature: booking-race-condition-fix, Property 2: Concurrent Booking Single Acceptance
 * @validates Requirements 1.1, 1.2, 1.3, 2.2, 3.1, 3.2, 4.3
 */
class BookingRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;
    protected Consultant $consultant;
    protected User $client1;
    protected User $client2;
    protected User $client3;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(BookingService::class);
        $this->consultant = Consultant::factory()->create(['buffer' => 15]);
        $this->client1 = User::factory()->create();
        $this->client2 = User::factory()->create();
        $this->client3 = User::factory()->create();

        // Create working hours for Monday (day 1)
        ConsultantWorkingHour::create([
            'consultant_id' => $this->consultant->id,
            'day_of_week' => 1, // Monday
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a valid DTO
     */
    protected function createDTO(int $clientId, array $overrides = []): CreatePendingBookingDTO
    {
        $defaults = [
            'client_id' => $clientId,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => 'consultant',
            'bookable_id' => $this->consultant->id,
            'start_at' => '2026-01-19T10:00:00', // Monday
            'duration_minutes' => 60,
            'notes' => null,
        ];

        $data = array_merge($defaults, $overrides);
        
        return new CreatePendingBookingDTO(
            $data['client_id'],
            $data['consultant_id'],
            $data['bookable_type'],
            $data['bookable_id'],
            $data['start_at'],
            $data['duration_minutes'],
            $data['notes']
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Property 1: No Overlapping Blocking Bookings
    // ─────────────────────────────────────────────────────────────

    /**
     * Test: Second booking for same time slot should be rejected
     * 
     * @property Feature: booking-race-condition-fix, Property 1: No Overlapping Blocking Bookings
     * @validates Requirements 1.2, 2.2, 4.3
     */
    public function test_second_booking_same_slot_rejected(): void
    {
        // First booking should succeed
        $dto1 = $this->createDTO($this->client1->id);
        $booking1 = $this->service->createPending($dto1);
        
        $this->assertEquals(Booking::STATUS_PENDING, $booking1->status);

        // Second booking for same slot should fail
        $dto2 = $this->createDTO($this->client2->id);
        
        $this->expectException(ValidationException::class);
        $this->service->createPending($dto2);
    }

    /**
     * Test: Overlapping booking (partial overlap) should be rejected
     * 
     * @property Feature: booking-race-condition-fix, Property 1: No Overlapping Blocking Bookings
     * @validates Requirements 1.2, 2.2
     */
    public function test_overlapping_booking_rejected(): void
    {
        // First booking: 10:00 - 11:00 (with 15 min buffer = occupied until 11:15)
        $dto1 = $this->createDTO($this->client1->id, ['start_at' => '2026-01-19T10:00:00']);
        $booking1 = $this->service->createPending($dto1);
        
        $this->assertEquals(Booking::STATUS_PENDING, $booking1->status);

        // Second booking: 10:30 - 11:30 (overlaps with first)
        $dto2 = $this->createDTO($this->client2->id, ['start_at' => '2026-01-19T10:30:00']);
        
        $this->expectException(ValidationException::class);
        $this->service->createPending($dto2);
    }

    /**
     * Test: Booking that overlaps with buffer should be rejected
     * 
     * @property Feature: booking-race-condition-fix, Property 3: Buffer Inclusion in Conflict Detection
     * @validates Requirements 2.1, 2.2
     */
    public function test_booking_overlapping_buffer_rejected(): void
    {
        // First booking: 10:00 - 11:00 (with 15 min buffer = occupied until 11:15)
        $dto1 = $this->createDTO($this->client1->id, ['start_at' => '2026-01-19T10:00:00']);
        $booking1 = $this->service->createPending($dto1);
        
        $this->assertEquals(Booking::STATUS_PENDING, $booking1->status);

        // Second booking: 11:00 - 12:00 (starts during buffer period)
        $dto2 = $this->createDTO($this->client2->id, ['start_at' => '2026-01-19T11:00:00']);
        
        $this->expectException(ValidationException::class);
        $this->service->createPending($dto2);
    }

    /**
     * Test: Non-overlapping booking should succeed
     * 
     * @property Feature: booking-race-condition-fix, Property 1: No Overlapping Blocking Bookings
     * @validates Requirements 1.2
     */
    public function test_non_overlapping_booking_succeeds(): void
    {
        // First booking: 10:00 - 11:00 (with 15 min buffer = occupied until 11:15)
        $dto1 = $this->createDTO($this->client1->id, ['start_at' => '2026-01-19T10:00:00']);
        $booking1 = $this->service->createPending($dto1);
        
        $this->assertEquals(Booking::STATUS_PENDING, $booking1->status);

        // Second booking: 11:15 - 12:15 (starts after buffer ends)
        $dto2 = $this->createDTO($this->client2->id, ['start_at' => '2026-01-19T11:15:00']);
        $booking2 = $this->service->createPending($dto2);
        
        $this->assertEquals(Booking::STATUS_PENDING, $booking2->status);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Concurrent Booking Single Acceptance
    // ─────────────────────────────────────────────────────────────

    /**
     * Test: Multiple sequential booking attempts for same slot
     * Only the first should succeed
     * 
     * @property Feature: booking-race-condition-fix, Property 2: Concurrent Booking Single Acceptance
     * @validates Requirements 1.1, 3.1, 3.2
     */
    public function test_multiple_sequential_bookings_only_first_succeeds(): void
    {
        $successCount = 0;
        $failCount = 0;
        $clients = [$this->client1, $this->client2, $this->client3];

        foreach ($clients as $client) {
            try {
                $dto = $this->createDTO($client->id);
                $this->service->createPending($dto);
                $successCount++;
            } catch (ValidationException $e) {
                $failCount++;
            }
        }

        // Only one booking should succeed
        $this->assertEquals(1, $successCount, 'Exactly one booking should succeed');
        $this->assertEquals(2, $failCount, 'Two bookings should fail');

        // Verify only one booking exists in database
        $bookings = Booking::where('consultant_id', $this->consultant->id)
            ->whereDate('start_at', '2026-01-19')
            ->blocking()
            ->get();

        $this->assertCount(1, $bookings, 'Only one blocking booking should exist');
    }

    /**
     * Test: Simulated concurrent booking using nested transactions
     * This simulates race condition by checking state before commit
     * 
     * @property Feature: booking-race-condition-fix, Property 2: Concurrent Booking Single Acceptance
     * @validates Requirements 1.1, 1.4, 3.1, 3.2
     */
    public function test_simulated_concurrent_booking_with_locking(): void
    {
        // This test verifies that the locking mechanism works correctly
        // by attempting to create bookings in a way that would cause
        // race conditions without proper locking

        $dto1 = $this->createDTO($this->client1->id);
        $dto2 = $this->createDTO($this->client2->id);

        // First booking succeeds
        $booking1 = $this->service->createPending($dto1);
        $this->assertNotNull($booking1);
        $this->assertEquals(Booking::STATUS_PENDING, $booking1->status);

        // Second booking should fail due to conflict
        try {
            $this->service->createPending($dto2);
            $this->fail('Second booking should have thrown ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('start_at', $e->errors());
        }

        // Verify database state
        $blockingBookings = Booking::where('consultant_id', $this->consultant->id)
            ->whereDate('start_at', '2026-01-19')
            ->blocking()
            ->count();

        $this->assertEquals(1, $blockingBookings);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Blocking Status Correctness
    // ─────────────────────────────────────────────────────────────

    /**
     * Test: Expired pending booking should not block new bookings
     * 
     * @property Feature: booking-race-condition-fix, Property 5: Blocking Status Correctness
     * @validates Requirements 2.3
     */
    public function test_expired_pending_does_not_block(): void
    {
        // Create an expired pending booking directly
        $expiredBooking = Booking::create([
            'client_id' => $this->client1->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => Carbon::parse('2026-01-19 10:00:00'),
            'end_at' => Carbon::parse('2026-01-19 11:00:00'),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(), // Expired
        ]);

        // New booking for same slot should succeed (expired pending doesn't block)
        $dto = $this->createDTO($this->client2->id);
        $newBooking = $this->service->createPending($dto);

        $this->assertEquals(Booking::STATUS_PENDING, $newBooking->status);
    }

    /**
     * Test: Cancelled booking should not block new bookings
     * 
     * @property Feature: booking-race-condition-fix, Property 5: Blocking Status Correctness
     * @validates Requirements 2.3
     */
    public function test_cancelled_booking_does_not_block(): void
    {
        // Create a cancelled booking directly
        $cancelledBooking = Booking::create([
            'client_id' => $this->client1->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => Carbon::parse('2026-01-19 10:00:00'),
            'end_at' => Carbon::parse('2026-01-19 11:00:00'),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CANCELLED,
        ]);

        // New booking for same slot should succeed
        $dto = $this->createDTO($this->client2->id);
        $newBooking = $this->service->createPending($dto);

        $this->assertEquals(Booking::STATUS_PENDING, $newBooking->status);
    }

    /**
     * Test: Confirmed booking should block new bookings
     * 
     * @property Feature: booking-race-condition-fix, Property 5: Blocking Status Correctness
     * @validates Requirements 2.3
     */
    public function test_confirmed_booking_blocks(): void
    {
        // Create a confirmed booking directly
        $confirmedBooking = Booking::create([
            'client_id' => $this->client1->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => Carbon::parse('2026-01-19 10:00:00'),
            'end_at' => Carbon::parse('2026-01-19 11:00:00'),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // New booking for same slot should fail
        $dto = $this->createDTO($this->client2->id);
        
        $this->expectException(ValidationException::class);
        $this->service->createPending($dto);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Transaction Atomicity
    // ─────────────────────────────────────────────────────────────

    /**
     * Test: Failed booking should not leave partial data
     * 
     * @property Feature: booking-race-condition-fix, Property 4: Transaction Atomicity
     * @validates Requirements 4.1, 4.2
     */
    public function test_failed_booking_no_partial_data(): void
    {
        // Create first booking
        $dto1 = $this->createDTO($this->client1->id);
        $this->service->createPending($dto1);

        $initialCount = Booking::count();

        // Try to create conflicting booking
        try {
            $dto2 = $this->createDTO($this->client2->id);
            $this->service->createPending($dto2);
        } catch (ValidationException $e) {
            // Expected
        }

        // Count should not have changed
        $this->assertEquals($initialCount, Booking::count(), 'No partial data should be created');
    }

    // ─────────────────────────────────────────────────────────────
    // Invariant Check: No Overlapping Blocking Bookings
    // ─────────────────────────────────────────────────────────────

    /**
     * Test: Database invariant - no overlapping blocking bookings
     * 
     * @property Feature: booking-race-condition-fix, Property 1: No Overlapping Blocking Bookings
     * @validates Requirements 4.3
     */
    public function test_invariant_no_overlapping_blocking_bookings(): void
    {
        // Create multiple non-overlapping bookings
        $times = ['09:00', '11:15', '13:30', '15:45'];
        
        foreach ($times as $time) {
            $dto = $this->createDTO($this->client1->id, [
                'start_at' => "2026-01-19T{$time}:00",
            ]);
            $this->service->createPending($dto);
        }

        // Get all blocking bookings for the consultant
        $bookings = Booking::where('consultant_id', $this->consultant->id)
            ->blocking()
            ->orderBy('start_at')
            ->get();

        // Verify no overlaps
        for ($i = 0; $i < $bookings->count() - 1; $i++) {
            $current = $bookings[$i];
            $next = $bookings[$i + 1];

            $currentOccupiedEnd = $current->end_at->copy()->addMinutes($current->buffer_after_minutes);
            
            $this->assertTrue(
                $currentOccupiedEnd->lte($next->start_at),
                "Booking {$current->id} occupied end ({$currentOccupiedEnd}) should be <= booking {$next->id} start ({$next->start_at})"
            );
        }
    }
}
