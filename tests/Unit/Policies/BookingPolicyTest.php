<?php

namespace Tests\Unit\Policies;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\User;
use App\Policies\BookingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for BookingPolicy
 * 
 * @property Feature: bookings-backend, Property 12: Authorization Invariants
 * @validates Requirements 13.1, 13.2, 13.3, 13.4, 13.5, 13.6
 */
class BookingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected BookingPolicy $policy;
    protected User $client;
    protected User $consultantUser;
    protected Consultant $consultant;
    protected User $otherUser;
    protected Admin $admin;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new BookingPolicy();
        
        // Create client
        $this->client = User::factory()->create();
        
        // Create consultant user and consultant record
        $this->consultantUser = User::factory()->create();
        $this->consultant = Consultant::factory()->create([
            'user_id' => $this->consultantUser->id,
        ]);
        
        // Create another user (not related to booking)
        $this->otherUser = User::factory()->create();
        
        // Create admin
        $this->admin = Admin::factory()->create();
        
        // Create booking
        $this->booking = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // View Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Client can view their own bookings
     */
    public function test_client_can_view_own_booking(): void
    {
        $this->assertTrue($this->policy->view($this->client, $this->booking));
    }

    /**
     * Property: Consultant can view bookings for their consultations
     */
    public function test_consultant_can_view_their_consultation_booking(): void
    {
        $this->assertTrue($this->policy->view($this->consultantUser, $this->booking));
    }

    /**
     * Property: Other users cannot view booking
     */
    public function test_other_user_cannot_view_booking(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->booking));
    }

    /**
     * Property: Admin can view any booking
     */
    public function test_admin_can_view_any_booking(): void
    {
        $this->assertTrue($this->policy->viewAsAdmin($this->admin, $this->booking));
    }

    // ─────────────────────────────────────────────────────────────
    // Cancel Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Client can cancel their own bookings
     */
    public function test_client_can_cancel_own_booking(): void
    {
        $this->assertTrue($this->policy->cancel($this->client, $this->booking));
    }

    /**
     * Property: Consultant can cancel their consultation bookings
     */
    public function test_consultant_can_cancel_their_consultation_booking(): void
    {
        $this->assertTrue($this->policy->cancel($this->consultantUser, $this->booking));
    }

    /**
     * Property: Other users cannot cancel booking
     */
    public function test_other_user_cannot_cancel_booking(): void
    {
        $this->assertFalse($this->policy->cancel($this->otherUser, $this->booking));
    }

    /**
     * Property: Admin can cancel any booking
     */
    public function test_admin_can_cancel_any_booking(): void
    {
        $this->assertTrue($this->policy->cancelAsAdmin($this->admin, $this->booking));
    }

    // ─────────────────────────────────────────────────────────────
    // Create Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Any authenticated user can create bookings
     */
    public function test_any_user_can_create_booking(): void
    {
        $this->assertTrue($this->policy->create($this->client));
        $this->assertTrue($this->policy->create($this->consultantUser));
        $this->assertTrue($this->policy->create($this->otherUser));
    }

    // ─────────────────────────────────────────────────────────────
    // Confirm Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: Only client can confirm their booking
     */
    public function test_only_client_can_confirm_booking(): void
    {
        $this->assertTrue($this->policy->confirm($this->client, $this->booking));
        $this->assertFalse($this->policy->confirm($this->consultantUser, $this->booking));
        $this->assertFalse($this->policy->confirm($this->otherUser, $this->booking));
    }

    // ─────────────────────────────────────────────────────────────
    // Cross-booking Authorization
    // ─────────────────────────────────────────────────────────────

    /**
     * Property: User cannot view/cancel another user's booking
     */
    public function test_user_cannot_access_others_booking(): void
    {
        // Create another booking for a different client
        $anotherClient = User::factory()->create();
        $anotherBooking = Booking::create([
            'client_id' => $anotherClient->id,
            'consultant_id' => $this->consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $this->consultant->id,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Original client cannot view/cancel another client's booking
        $this->assertFalse($this->policy->view($this->client, $anotherBooking));
        $this->assertFalse($this->policy->cancel($this->client, $anotherBooking));

        // But consultant can (it's their consultation)
        $this->assertTrue($this->policy->view($this->consultantUser, $anotherBooking));
        $this->assertTrue($this->policy->cancel($this->consultantUser, $anotherBooking));
    }

    /**
     * Property: Consultant cannot access bookings for other consultants
     */
    public function test_consultant_cannot_access_other_consultant_bookings(): void
    {
        // Create another consultant
        $anotherConsultantUser = User::factory()->create();
        $anotherConsultant = Consultant::factory()->create([
            'user_id' => $anotherConsultantUser->id,
        ]);

        // Create booking for another consultant
        $anotherBooking = Booking::create([
            'client_id' => $this->client->id,
            'consultant_id' => $anotherConsultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $anotherConsultant->id,
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Original consultant cannot view/cancel booking for another consultant
        $this->assertFalse($this->policy->view($this->consultantUser, $anotherBooking));
        $this->assertFalse($this->policy->cancel($this->consultantUser, $anotherBooking));

        // But the other consultant can
        $this->assertTrue($this->policy->view($anotherConsultantUser, $anotherBooking));
        $this->assertTrue($this->policy->cancel($anotherConsultantUser, $anotherBooking));
    }
}
