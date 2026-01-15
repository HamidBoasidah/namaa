<?php

namespace Tests\Unit\Services;

use App\DTOs\CreatePendingBookingDTO;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\ConsultantWorkingHour;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Property tests for BookingService
 * 
 * @property Feature: bookings-backend, Property 1: Time Granularity Validation
 * @property Feature: bookings-backend, Property 2: Booking Type Duration Resolution
 * @property Feature: bookings-backend, Property 3: Buffer Resolution and Snapshot
 * @property Feature: bookings-backend, Property 7: Pending Booking Lifecycle
 * @property Feature: bookings-backend, Property 8: Expiration Job Correctness
 * @property Feature: bookings-backend, Property 9: Confirmation State Transitions
 * @property Feature: bookings-backend, Property 10: Cancellation with Polymorphic Canceller
 * @validates Requirements 2.1-2.5, 3.1-3.5, 8.1-8.3, 9.1-9.6, 10.1-10.6, 11.2-11.3
 */
class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;
    protected Consultant $consultant;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(BookingService::class);
        $this->consultant = Consultant::factory()->create(['buffer' => 15]);
        $this->client = User::factory()->create();

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
    protected function createDTO(array $overrides = []): CreatePendingBookingDTO
    {
        $defaults = [
            'client_id' => $this->client->id,
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
    // Property 1: Time Granularity Validation
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Start time must be multiple of 5 minutes
     */
    public function test_start_time_must_be_multiple_of_5(): void
    {
        // Valid: 10:00, 10:05, 10:10, etc.
        $validTimes = ['10:00', '10:05', '10:10', '10:15', '10:30', '10:45'];
        
        foreach ($validTimes as $time) {
            $dto = $this->createDTO(['start_at' => "2026-01-19T{$time}:00"]);
            $booking = $this->service->createPending($dto);
            $this->assertEquals(Booking::STATUS_PENDING, $booking->status);
            $booking->forceDelete(); // Clean up for next iteration
        }
    }

    /**
     * Property: Invalid start time (not multiple of 5) is rejected
     */
    public function test_invalid_start_time_rejected(): void
    {
        $invalidTimes = ['10:01', '10:02', '10:03', '10:04', '10:07', '10:13'];
        
        foreach ($invalidTimes as $time) {
            $dto = $this->createDTO(['start_at' => "2026-01-19T{$time}:00"]);
            
            $this->expectException(ValidationException::class);
            $this->service->createPending($dto);
        }
    }

    /**
     * Property: Duration must be multiple of 5 minutes
     */
    public function test_duration_must_be_multiple_of_5(): void
    {
        $validDurations = [30, 45, 60, 90, 120];
        
        foreach ($validDurations as $duration) {
            $dto = $this->createDTO([
                'duration_minutes' => $duration,
                'start_at' => '2026-01-19T09:00:00', // Early enough to fit any duration
            ]);
            $booking = $this->service->createPending($dto);
            $this->assertEquals($duration, $booking->duration_minutes);
            $booking->forceDelete();
        }
    }

    /**
     * Property: Invalid duration (not multiple of 5) is rejected
     */
    public function test_invalid_duration_rejected(): void
    {
        $dto = $this->createDTO(['duration_minutes' => 37]);
        
        $this->expectException(ValidationException::class);
        $this->service->createPending($dto);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2 & 3: Duration and Buffer Resolution
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: ConsultantService booking uses service duration
     */
    public function test_service_booking_uses_service_duration(): void
    {
        $service = ConsultantService::factory()->create([
            'consultant_id' => $this->consultant->id,
            'duration_minutes' => 45,
            'buffer' => 10,
        ]);

        $dto = $this->createDTO([
            'bookable_type' => 'consultant_service',
            'bookable_id' => $service->id,
            'duration_minutes' => 120, // Should be ignored
        ]);

        $booking = $this->service->createPending($dto);

        $this->assertEquals(45, $booking->duration_minutes);
        $this->assertEquals(10, $booking->buffer_after_minutes);
    }

    /**
     * Property: Direct consultant booking uses user-provided duration
     */
    public function test_consultant_booking_uses_user_duration(): void
    {
        $dto = $this->createDTO([
            'bookable_type' => 'consultant',
            'bookable_id' => $this->consultant->id,
            'duration_minutes' => 90,
        ]);

        $booking = $this->service->createPending($dto);

        $this->assertEquals(90, $booking->duration_minutes);
        $this->assertEquals($this->consultant->buffer, $booking->buffer_after_minutes);
    }

    /**
     * Property: Direct consultant booking requires duration
     */
    public function test_consultant_booking_requires_duration(): void
    {
        $dto = $this->createDTO([
            'bookable_type' => 'consultant',
            'bookable_id' => $this->consultant->id,
            'duration_minutes' => null,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->createPending($dto);
    }

    /**
     * Property: Service booking uses service buffer when set
     * Note: Database schema has buffer NOT NULL with default 0
     * When buffer is 0, it uses 0 (no fallback to consultant buffer)
     */
    public function test_service_uses_own_buffer_when_set(): void
    {
        $service = ConsultantService::factory()->create([
            'consultant_id' => $this->consultant->id,
            'duration_minutes' => 30,
            'buffer' => 20, // Service has its own buffer
        ]);

        $dto = $this->createDTO([
            'bookable_type' => 'consultant_service',
            'bookable_id' => $service->id,
        ]);

        $booking = $this->service->createPending($dto);

        // Should use service buffer, not consultant buffer
        $this->assertEquals(20, $booking->buffer_after_minutes);
        $this->assertNotEquals($this->consultant->buffer, $booking->buffer_after_minutes);
    }

    /**
     * Property: Buffer is snapshot at creation time
     */
    public function test_buffer_is_snapshot(): void
    {
        $originalBuffer = $this->consultant->buffer;
        
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        // Change consultant buffer
        $this->consultant->update(['buffer' => 30]);

        // Booking should still have original buffer
        $this->assertEquals($originalBuffer, $booking->fresh()->buffer_after_minutes);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 7: Pending Booking Lifecycle
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: New booking has pending status and expires_at set
     */
    public function test_new_booking_is_pending_with_expiry(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        $this->assertEquals(Booking::STATUS_PENDING, $booking->status);
        $this->assertNotNull($booking->expires_at);
        
        // Expires in approximately 15 minutes
        $expectedExpiry = now()->addMinutes(Booking::PENDING_HOLD_MINUTES);
        $this->assertTrue($booking->expires_at->diffInSeconds($expectedExpiry) < 5);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 8: Expiration Job Correctness
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Expiration job only affects expired pending bookings
     */
    public function test_expiration_job_only_affects_expired_pending(): void
    {
        // Create expired pending
        $expiredPending = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        // Create valid pending
        $validPending = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Create confirmed
        $confirmed = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $count = $this->service->expireOldPending();

        $this->assertEquals(1, $count);
        $this->assertEquals(Booking::STATUS_EXPIRED, $expiredPending->fresh()->status);
        $this->assertEquals(Booking::STATUS_PENDING, $validPending->fresh()->status);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $confirmed->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 9: Confirmation State Transitions
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Only pending bookings can be confirmed
     */
    public function test_only_pending_can_be_confirmed(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        // Confirm should work
        $confirmed = $this->service->confirm($booking->id, $this->client->id);
        $this->assertEquals(Booking::STATUS_CONFIRMED, $confirmed->status);
        $this->assertNull($confirmed->expires_at);

        // Try to confirm again - should fail
        $this->expectException(ValidationException::class);
        $this->service->confirm($booking->id, $this->client->id);
    }

    /**
     * Property: Expired pending booking cannot be confirmed
     */
    public function test_expired_pending_cannot_be_confirmed(): void
    {
        $booking = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->subMinute(), // Expired
        ]);

        $this->expectException(ValidationException::class);
        $this->service->confirm($booking->id, $this->client->id);
    }

    /**
     * Property: Confirmation clears expires_at
     */
    public function test_confirmation_clears_expires_at(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);
        
        $this->assertNotNull($booking->expires_at);

        $confirmed = $this->service->confirm($booking->id, $this->client->id);
        
        $this->assertNull($confirmed->expires_at);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 10: Cancellation with Polymorphic Canceller
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Cancellation sets correct status and timestamp
     */
    public function test_cancellation_sets_status_and_timestamp(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        $cancelled = $this->service->cancel($booking->id, $this->client, 'Test reason');

        $this->assertEquals(Booking::STATUS_CANCELLED, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertEquals('Test reason', $cancelled->cancel_reason);
    }

    /**
     * Property: Cancellation stores polymorphic canceller (User)
     */
    public function test_cancellation_stores_user_canceller(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        $cancelled = $this->service->cancel($booking->id, $this->client);

        $this->assertEquals(User::class, $cancelled->cancelled_by_type);
        $this->assertEquals($this->client->id, $cancelled->cancelled_by_id);
        $this->assertInstanceOf(User::class, $cancelled->cancelledBy);
    }

    /**
     * Property: Cancellation stores polymorphic canceller (Admin)
     */
    public function test_cancellation_stores_admin_canceller(): void
    {
        $admin = Admin::factory()->create();
        
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);

        $cancelled = $this->service->cancel($booking->id, $admin);

        $this->assertEquals(Admin::class, $cancelled->cancelled_by_type);
        $this->assertEquals($admin->id, $cancelled->cancelled_by_id);
    }

    /**
     * Property: Cancelled booking cannot be cancelled again
     */
    public function test_cancelled_booking_cannot_be_cancelled(): void
    {
        $dto = $this->createDTO();
        $booking = $this->service->createPending($dto);
        
        $this->service->cancel($booking->id, $this->client);

        $this->expectException(ValidationException::class);
        $this->service->cancel($booking->id, $this->client);
    }

    /**
     * Property: Completed booking cannot be cancelled
     */
    public function test_completed_booking_cannot_be_cancelled(): void
    {
        $booking = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->subDay(),
            'end_at' => now()->subDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->cancel($booking->id, $this->client);
    }
}
