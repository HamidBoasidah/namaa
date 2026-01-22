<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for MessagePolicy
 * 
 * @validates Requirements 11.2, 14.4
 */
class MessagePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected MessagePolicy $policy;
    protected User $client;
    protected User $consultantUser;
    protected Consultant $consultant;
    protected User $otherUser;
    protected Booking $booking;
    protected Conversation $conversation;
    protected Message $message;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new MessagePolicy();
        
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
        
        // Create a message in the conversation
        $this->message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // View Authorization Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that client can view message in their conversation
     * 
     * @validates Requirement 11.2
     */
    public function test_client_can_view_message(): void
    {
        $this->assertTrue($this->policy->view($this->client, $this->message));
    }

    /**
     * Test that consultant can view message in their conversation
     * 
     * @validates Requirement 11.2
     */
    public function test_consultant_can_view_message(): void
    {
        $this->assertTrue($this->policy->view($this->consultantUser, $this->message));
    }

    /**
     * Test that non-participant cannot view message
     * 
     * @validates Requirement 11.2
     */
    public function test_non_participant_cannot_view_message(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->message));
    }

    /**
     * Test that participant can view message sent by other participant
     * 
     * @validates Requirement 11.2
     */
    public function test_participant_can_view_message_from_other_participant(): void
    {
        // Create message from consultant
        $consultantMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->consultantUser->id,
            'body' => 'Consultant message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Client should be able to view consultant's message
        $this->assertTrue($this->policy->view($this->client, $consultantMessage));
        
        // Consultant should be able to view their own message
        $this->assertTrue($this->policy->view($this->consultantUser, $consultantMessage));
        
        // Other user should not be able to view
        $this->assertFalse($this->policy->view($this->otherUser, $consultantMessage));
    }
}
