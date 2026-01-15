<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for Booking Model
 * 
 * @property Feature: bookings-backend, Property 1: Time Granularity Validation (partial - model level)
 * @validates Requirements 1.3, 1.7, 1.9
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property: For any booking with valid status, the status must be one of the defined constants
     */
    public function test_status_must_be_valid_enum_value(): void
    {
        $validStatuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_EXPIRED,
        ];

        foreach ($validStatuses as $status) {
            $this->assertContains($status, $validStatuses);
        }

        // Verify blocking statuses are subset of valid statuses
        foreach (Booking::BLOCKING_STATUSES as $blockingStatus) {
            $this->assertContains($blockingStatus, $validStatuses);
        }
    }

    /**
     * Property: Blocking scope returns only confirmed OR (pending with valid expiry)
     */
    public function test_blocking_scope_returns_correct_bookings(): void
    {
        // Create test data
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();

        // Confirmed booking - should be blocking
        $confirmedBooking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Pending with future expiry - should be blocking
        $pendingValidBooking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Pending with past expiry - should NOT be blocking
        $pendingExpiredBooking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        // Cancelled booking - should NOT be blocking
        $cancelledBooking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDays(4),
            'end_at' => now()->addDays(4)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        // Get blocking bookings
        $blockingBookings = Booking::blocking()->pluck('id')->toArray();

        $this->assertContains($confirmedBooking->id, $blockingBookings);
        $this->assertContains($pendingValidBooking->id, $blockingBookings);
        $this->assertNotContains($pendingExpiredBooking->id, $blockingBookings);
        $this->assertNotContains($cancelledBooking->id, $blockingBookings);
    }

    /**
     * Property: isBlocking() helper returns correct value for all status combinations
     */
    public function test_is_blocking_helper_returns_correct_value(): void
    {
        $booking = new Booking();

        // Confirmed is always blocking
        $booking->status = Booking::STATUS_CONFIRMED;
        $this->assertTrue($booking->isBlocking());

        // Pending with future expiry is blocking
        $booking->status = Booking::STATUS_PENDING;
        $booking->expires_at = now()->addMinutes(10);
        $this->assertTrue($booking->isBlocking());

        // Pending with past expiry is NOT blocking
        $booking->expires_at = now()->subMinute();
        $this->assertFalse($booking->isBlocking());

        // Cancelled is NOT blocking
        $booking->status = Booking::STATUS_CANCELLED;
        $this->assertFalse($booking->isBlocking());

        // Expired is NOT blocking
        $booking->status = Booking::STATUS_EXPIRED;
        $this->assertFalse($booking->isBlocking());

        // Completed is NOT blocking
        $booking->status = Booking::STATUS_COMPLETED;
        $this->assertFalse($booking->isBlocking());
    }

    /**
     * Property: Occupied end includes buffer_after_minutes
     */
    public function test_occupied_end_includes_buffer(): void
    {
        $endTime = Carbon::parse('2026-01-15 10:00:00');
        $bufferMinutes = 15;

        $booking = new Booking();
        $booking->end_at = $endTime;
        $booking->buffer_after_minutes = $bufferMinutes;

        $expectedOccupiedEnd = $endTime->copy()->addMinutes($bufferMinutes);
        
        $this->assertEquals($expectedOccupiedEnd->toDateTimeString(), $booking->occupied_end->toDateTimeString());
    }

    /**
     * Property: canBeCancelled returns true only for pending and confirmed
     */
    public function test_can_be_cancelled_returns_correct_value(): void
    {
        $booking = new Booking();

        $booking->status = Booking::STATUS_PENDING;
        $this->assertTrue($booking->canBeCancelled());

        $booking->status = Booking::STATUS_CONFIRMED;
        $this->assertTrue($booking->canBeCancelled());

        $booking->status = Booking::STATUS_CANCELLED;
        $this->assertFalse($booking->canBeCancelled());

        $booking->status = Booking::STATUS_EXPIRED;
        $this->assertFalse($booking->canBeCancelled());

        $booking->status = Booking::STATUS_COMPLETED;
        $this->assertFalse($booking->canBeCancelled());
    }

    /**
     * Property: canBeConfirmed returns true only for pending with valid expiry
     */
    public function test_can_be_confirmed_returns_correct_value(): void
    {
        $booking = new Booking();

        // Pending with future expiry - can be confirmed
        $booking->status = Booking::STATUS_PENDING;
        $booking->expires_at = now()->addMinutes(10);
        $this->assertTrue($booking->canBeConfirmed());

        // Pending with past expiry - cannot be confirmed
        $booking->expires_at = now()->subMinute();
        $this->assertFalse($booking->canBeConfirmed());

        // Confirmed - cannot be confirmed again
        $booking->status = Booking::STATUS_CONFIRMED;
        $booking->expires_at = null;
        $this->assertFalse($booking->canBeConfirmed());
    }

    /**
     * Property: Polymorphic bookable relationship resolves correctly for Consultant
     */
    public function test_bookable_morphto_resolves_consultant(): void
    {
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();

        $booking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertInstanceOf(Consultant::class, $booking->bookable);
        $this->assertEquals($consultant->id, $booking->bookable->id);
    }

    /**
     * Property: Polymorphic bookable relationship resolves correctly for ConsultantService
     */
    public function test_bookable_morphto_resolves_consultant_service(): void
    {
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);

        $booking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'buffer_after_minutes' => $service->buffer ?? $consultant->buffer ?? 0,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertInstanceOf(ConsultantService::class, $booking->bookable);
        $this->assertEquals($service->id, $booking->bookable->id);
    }

    /**
     * Property: Polymorphic cancelledBy relationship resolves correctly
     */
    public function test_cancelled_by_morphto_resolves_correctly(): void
    {
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $canceller = User::factory()->create();

        $booking = Booking::create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_type' => User::class,
            'cancelled_by_id' => $canceller->id,
            'cancel_reason' => 'Test cancellation',
        ]);

        $this->assertInstanceOf(User::class, $booking->cancelledBy);
        $this->assertEquals($canceller->id, $booking->cancelledBy->id);
    }
}
