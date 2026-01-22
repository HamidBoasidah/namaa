<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\User;
use App\Policies\ConversationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ConversationPolicy
 * 
 * @validates Requirements 2.1, 11.1, 11.2, 14.4
 */
class ConversationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ConversationPolicy $policy;
    protected User $client;
    protected User $consultantUser;
    protected Consultant $consultant;
    protected User $otherUser;
    protected Booking $booking;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new ConversationPolicy();
        
        // Create client
        $this->client = User::factory()->create();
        
        // Create consultant user and consultant record
        $this->consultantUser = User::factory()->create();
        $this->consultant = Consultant::factory()->create([
            'user_id' => $this->consultantUser->id,
        ]);
        
        // Create another user (not related to booking)
        $this->otherUser = User::factory()->create();
        
        // Create confirmed booking
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
        
        // Create conversation with participants
        $this->conversation = Conversation::create([
            'booking_id' => $this->booking->id,
        ]);
        
        $this->conversation->participants()->attach([
            $this->client->id,
            $this->consultantUser->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // View Authorization Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that client can view conversation
     * 
     * @validates Requirement 11.1
     */
    public function test_client_can_view_conversation(): void
    {
        $this->assertTrue($this->policy->view($this->client, $this->conversation));
    }

    /**
     * Test that consultant can view conversation
     * 
     * @validates Requirement 11.1
     */
    public function test_consultant_can_view_conversation(): void
    {
        $this->assertTrue($this->policy->view($this->consultantUser, $this->conversation));
    }

    /**
     * Test that non-participant cannot view conversation
     * 
     * @validates Requirement 11.1
     */
    public function test_non_participant_cannot_view_conversation(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->conversation));
    }

    // ─────────────────────────────────────────────────────────────
    // Send Message Authorization Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that client can send message in confirmed booking
     * 
     * @validates Requirements 2.1, 11.2
     */
    public function test_client_can_send_message_in_confirmed_booking(): void
    {
        $this->assertTrue($this->policy->sendMessage($this->client, $this->conversation));
    }

    /**
     * Test that consultant can send message in confirmed booking
     * 
     * @validates Requirements 2.1, 11.2
     */
    public function test_consultant_can_send_message_in_confirmed_booking(): void
    {
        $this->assertTrue($this->policy->sendMessage($this->consultantUser, $this->conversation));
    }

    /**
     * Test that non-participant cannot send message
     * 
     * @validates Requirement 11.2
     */
    public function test_non_participant_cannot_send_message(): void
    {
        $this->assertFalse($this->policy->sendMessage($this->otherUser, $this->conversation));
    }

    /**
     * Test that participant cannot send message when booking is pending
     * 
     * @validates Requirement 2.2
     */
    public function test_participant_cannot_send_message_when_booking_pending(): void
    {
        $this->booking->update(['status' => Booking::STATUS_PENDING]);
        $this->conversation->refresh();

        $this->assertFalse($this->policy->sendMessage($this->client, $this->conversation));
        $this->assertFalse($this->policy->sendMessage($this->consultantUser, $this->conversation));
    }

    /**
     * Test that participant cannot send message when booking is cancelled
     * 
     * @validates Requirement 2.3
     */
    public function test_participant_cannot_send_message_when_booking_cancelled(): void
    {
        $this->booking->update(['status' => Booking::STATUS_CANCELLED]);
        $this->conversation->refresh();

        $this->assertFalse($this->policy->sendMessage($this->client, $this->conversation));
        $this->assertFalse($this->policy->sendMessage($this->consultantUser, $this->conversation));
    }

    /**
     * Test that participant cannot send message when booking is completed
     * 
     * @validates Requirement 2.4
     */
    public function test_participant_cannot_send_message_when_booking_completed(): void
    {
        $this->booking->update(['status' => Booking::STATUS_COMPLETED]);
        $this->conversation->refresh();

        $this->assertFalse($this->policy->sendMessage($this->client, $this->conversation));
        $this->assertFalse($this->policy->sendMessage($this->consultantUser, $this->conversation));
    }

    /**
     * Test that participant cannot send message when booking is expired
     * 
     * @validates Requirement 2.5
     */
    public function test_participant_cannot_send_message_when_booking_expired(): void
    {
        $this->booking->update(['status' => Booking::STATUS_EXPIRED]);
        $this->conversation->refresh();

        $this->assertFalse($this->policy->sendMessage($this->client, $this->conversation));
        $this->assertFalse($this->policy->sendMessage($this->consultantUser, $this->conversation));
    }

    /**
     * Test that only confirmed status allows messaging
     * 
     * @validates Requirement 2.1
     */
    public function test_only_confirmed_status_allows_messaging(): void
    {
        $statuses = [
            Booking::STATUS_CONFIRMED => true,
            Booking::STATUS_PENDING => false,
            Booking::STATUS_CANCELLED => false,
            Booking::STATUS_COMPLETED => false,
            Booking::STATUS_EXPIRED => false,
        ];

        foreach ($statuses as $status => $expected) {
            $this->booking->update(['status' => $status]);
            $this->conversation->refresh();

            $this->assertEquals(
                $expected,
                $this->policy->sendMessage($this->client, $this->conversation),
                "Client sendMessage should be {$expected} for status {$status}"
            );

            $this->assertEquals(
                $expected,
                $this->policy->sendMessage($this->consultantUser, $this->conversation),
                "Consultant sendMessage should be {$expected} for status {$status}"
            );
        }
    }
}

