<?php

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Policies\AttachmentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for AttachmentPolicy
 * 
 * @validates Requirements 10.6, 11.3, 14.4
 */
class AttachmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected AttachmentPolicy $policy;
    protected User $client;
    protected User $consultantUser;
    protected Consultant $consultant;
    protected User $otherUser;
    protected Booking $booking;
    protected Conversation $conversation;
    protected Message $message;
    protected MessageAttachment $attachment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new AttachmentPolicy();
        
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
            'body' => 'Test message with attachment',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);
        
        // Create an attachment for the message
        $this->attachment = MessageAttachment::create([
            'message_id' => $this->message->id,
            'original_name' => 'test-document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024000,
            'disk' => 'private',
            'path' => 'chat-attachments/1/1/test-document.pdf',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Download Authorization Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that client can download attachment from their conversation
     * 
     * @validates Requirements 10.6, 11.3
     */
    public function test_client_can_download_attachment(): void
    {
        $this->assertTrue($this->policy->download($this->client, $this->attachment));
    }

    /**
     * Test that consultant can download attachment from their conversation
     * 
     * @validates Requirements 10.6, 11.3
     */
    public function test_consultant_can_download_attachment(): void
    {
        $this->assertTrue($this->policy->download($this->consultantUser, $this->attachment));
    }

    /**
     * Test that non-participant cannot download attachment
     * 
     * @validates Requirements 10.6, 11.3
     */
    public function test_non_participant_cannot_download_attachment(): void
    {
        $this->assertFalse($this->policy->download($this->otherUser, $this->attachment));
    }

    /**
     * Test that participant can download attachment sent by other participant
     * 
     * @validates Requirements 10.6, 11.3
     */
    public function test_participant_can_download_attachment_from_other_participant(): void
    {
        // Create message from consultant with attachment
        $consultantMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->consultantUser->id,
            'body' => null,
            'type' => 'attachment',
            'context' => 'out_of_session',
        ]);

        $consultantAttachment = MessageAttachment::create([
            'message_id' => $consultantMessage->id,
            'original_name' => 'consultant-file.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 512000,
            'disk' => 'private',
            'path' => 'chat-attachments/1/2/consultant-file.jpg',
        ]);

        // Client should be able to download consultant's attachment
        $this->assertTrue($this->policy->download($this->client, $consultantAttachment));
        
        // Consultant should be able to download their own attachment
        $this->assertTrue($this->policy->download($this->consultantUser, $consultantAttachment));
        
        // Other user should not be able to download
        $this->assertFalse($this->policy->download($this->otherUser, $consultantAttachment));
    }

    /**
     * Test that authorization works across different conversations
     * 
     * @validates Requirements 10.6, 11.3
     */
    public function test_participant_cannot_download_attachment_from_different_conversation(): void
    {
        // Create another user and consultant
        $anotherClient = User::factory()->create();
        $anotherConsultantUser = User::factory()->create();
        $anotherConsultant = Consultant::factory()->create([
            'user_id' => $anotherConsultantUser->id,
        ]);

        // Create another booking and conversation
        $anotherBooking = Booking::create([
            'client_id' => $anotherClient->id,
            'consultant_id' => $anotherConsultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $anotherConsultant->id,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 15,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $anotherConversation = Conversation::create([
            'booking_id' => $anotherBooking->id,
        ]);

        $anotherConversation->participants()->attach([
            $anotherClient->id,
            $anotherConsultantUser->id,
        ]);

        // Create message and attachment in the other conversation
        $anotherMessage = Message::create([
            'conversation_id' => $anotherConversation->id,
            'sender_id' => $anotherClient->id,
            'body' => 'Another message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $anotherAttachment = MessageAttachment::create([
            'message_id' => $anotherMessage->id,
            'original_name' => 'another-document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048000,
            'disk' => 'private',
            'path' => 'chat-attachments/2/3/another-document.pdf',
        ]);

        // Original client and consultant should NOT be able to download from different conversation
        $this->assertFalse($this->policy->download($this->client, $anotherAttachment));
        $this->assertFalse($this->policy->download($this->consultantUser, $anotherAttachment));

        // Participants of the other conversation should be able to download
        $this->assertTrue($this->policy->download($anotherClient, $anotherAttachment));
        $this->assertTrue($this->policy->download($anotherConsultantUser, $anotherAttachment));
    }
}
