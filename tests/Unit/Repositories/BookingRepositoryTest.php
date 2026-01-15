<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\User;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for BookingRepository
 * 
 * @property Feature: bookings-backend, Property 6: Overlap Detection with Blocking Status
 * @validates Requirements 6.1, 6.2, 6.3, 6.4, 6.5
 */
class BookingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BookingRepository $repository;
    protected User $client;
    protected Consultant $consultant;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(BookingRepository::class);
        $this->client = User::factory()->create();
        $this->consultant = Consultant::factory()->create();
    }

    /**
     * Helper to create a booking
     */
    protected function createBooking(array $overrides = []): Booking
    {
        $defaults = [
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDay()->setTime(10, 0),
            'end_at' => now()->addDay()->setTime(11, 0),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ];

        return Booking::create(array_merge($defaults, $overrides));
    }

    /**
     * Property: Confirmed bookings are always detected as overlapping
     */
    public function test_confirmed_bookings_are_detected_as_overlapping(): void
    {
        // Create a confirmed booking from 10:00 to 11:00 with 15 min buffer
        // Occupied window: 10:00 - 11:15
        $existingBooking = $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Try to book 10:30 - 11:30 (overlaps with existing)
        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newEnd = Carbon::parse('2026-01-20 11:30:00');
        $newBuffer = 15;
        $newOccupiedEnd = $newEnd->copy()->addMinutes($newBuffer);

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(1, $overlaps);
        $this->assertEquals($existingBooking->id, $overlaps->first()->id);
    }

    /**
     * Property: Pending bookings with valid expiry are detected as overlapping
     */
    public function test_pending_with_valid_expiry_detected_as_overlapping(): void
    {
        $existingBooking = $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10), // Still valid
        ]);

        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(1, $overlaps);
    }

    /**
     * Property: Pending bookings with expired expiry are NOT detected as overlapping
     */
    public function test_pending_with_expired_expiry_not_detected(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(), // Expired
        ]);

        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Cancelled bookings are NOT detected as overlapping
     */
    public function test_cancelled_bookings_not_detected(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Expired bookings are NOT detected as overlapping
     */
    public function test_expired_bookings_not_detected(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'status' => Booking::STATUS_EXPIRED,
        ]);

        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Completed bookings are NOT detected as overlapping
     */
    public function test_completed_bookings_not_detected(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Buffer is included in overlap calculation
     * Existing: 10:00-11:00 with 15 min buffer = occupied until 11:15
     * New booking starting at 11:10 should overlap
     */
    public function test_buffer_included_in_overlap_calculation(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'buffer_after_minutes' => 15, // Occupied until 11:15
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // New booking 11:10 - 12:10 should overlap (starts before 11:15)
        $newStart = Carbon::parse('2026-01-20 11:10:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:25:00'); // 12:10 + 15 buffer

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(1, $overlaps);
    }

    /**
     * Property: Adjacent bookings (no overlap) are allowed
     * Existing: 10:00-11:00 with 15 min buffer = occupied until 11:15
     * New booking starting at 11:15 should NOT overlap
     */
    public function test_adjacent_bookings_allowed(): void
    {
        $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'buffer_after_minutes' => 15, // Occupied until 11:15
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // New booking 11:15 - 12:15 should NOT overlap (starts exactly at buffer end)
        $newStart = Carbon::parse('2026-01-20 11:15:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:30:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Bookings for different consultants don't overlap
     */
    public function test_different_consultant_bookings_dont_overlap(): void
    {
        $otherConsultant = Consultant::factory()->create();

        $this->createBooking([
            'consultant_id' => $otherConsultant->id,
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Check for overlaps with original consultant (should be none)
        $newStart = Carbon::parse('2026-01-20 10:30:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 12:00:00');

        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );

        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: Exclude booking ID works correctly (for confirmation re-check)
     */
    public function test_exclude_booking_id_works(): void
    {
        $booking = $this->createBooking([
            'start_at' => Carbon::parse('2026-01-20 10:00:00'),
            'end_at' => Carbon::parse('2026-01-20 11:00:00'),
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        $newStart = Carbon::parse('2026-01-20 10:00:00');
        $newOccupiedEnd = Carbon::parse('2026-01-20 11:15:00');

        // Without exclusion - should find overlap
        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd
        );
        $this->assertCount(1, $overlaps);

        // With exclusion - should not find overlap
        $overlaps = $this->repository->findBlockingOverlaps(
            $this->consultant->id,
            $newStart,
            $newOccupiedEnd,
            $booking->id
        );
        $this->assertCount(0, $overlaps);
    }

    /**
     * Property: expirePending updates only expired pending bookings
     */
    public function test_expire_pending_updates_only_expired(): void
    {
        // Expired pending
        $expiredPending = $this->createBooking([
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        // Valid pending
        $validPending = $this->createBooking([
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Confirmed (should not be affected)
        $confirmed = $this->createBooking([
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHour(),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $count = $this->repository->expirePending();

        $this->assertEquals(1, $count);
        $this->assertEquals(Booking::STATUS_EXPIRED, $expiredPending->fresh()->status);
        $this->assertEquals(Booking::STATUS_PENDING, $validPending->fresh()->status);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $confirmed->fresh()->status);
    }
}
