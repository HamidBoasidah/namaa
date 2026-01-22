<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function conversation_can_be_soft_deleted()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        
        // Create a conversation
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Soft delete the conversation
        $conversation->delete();

        // Assert the conversation is soft deleted
        $this->assertSoftDeleted('conversations', ['id' => $conversation->id]);
        $this->assertNotNull($conversation->fresh()->deleted_at);
    }

    /** @test */
    public function soft_deleted_conversation_can_be_restored()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        
        // Create and soft delete a conversation
        $conversation = Conversation::create(['booking_id' => $booking->id]);
        $conversation->delete();

        // Restore the conversation
        $conversation->restore();

        // Assert the conversation is restored
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function message_can_be_soft_deleted()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);
        
        // Create a message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Soft delete the message
        $message->delete();

        // Assert the message is soft deleted
        $this->assertSoftDeleted('messages', ['id' => $message->id]);
    }

    /** @test */
    public function message_attachment_can_be_soft_deleted()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);
        
        // Create an attachment
        $attachment = MessageAttachment::create([
            'message_id' => $message->id,
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'disk' => 'private',
            'path' => 'test/path/test.pdf',
        ]);

        // Soft delete the attachment
        $attachment->delete();

        // Assert the attachment is soft deleted
        $this->assertSoftDeleted('message_attachments', ['id' => $attachment->id]);
    }

    /** @test */
    public function only_trashed_scope_returns_soft_deleted_records()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking1 = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $booking2 = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        
        // Create two conversations
        $conversation1 = Conversation::create(['booking_id' => $booking1->id]);
        $conversation2 = Conversation::create(['booking_id' => $booking2->id]);

        // Soft delete one conversation
        $conversation1->delete();

        // Assert only trashed returns the deleted conversation
        $trashedConversations = Conversation::onlyTrashed()->get();
        $this->assertCount(1, $trashedConversations);
        $this->assertEquals($conversation1->id, $trashedConversations->first()->id);
    }

    /** @test */
    public function with_trashed_scope_returns_all_records()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking1 = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $booking2 = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        
        // Create two conversations
        $conversation1 = Conversation::create(['booking_id' => $booking1->id]);
        $conversation2 = Conversation::create(['booking_id' => $booking2->id]);

        // Soft delete one conversation
        $conversation1->delete();

        // Assert with trashed returns all conversations
        $allConversations = Conversation::withTrashed()->get();
        $this->assertCount(2, $allConversations);
    }

    /** @test */
    public function force_delete_permanently_removes_record()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        
        // Create a conversation
        $conversation = Conversation::create(['booking_id' => $booking->id]);
        $conversationId = $conversation->id;

        // Force delete the conversation
        $conversation->forceDelete();

        // Assert the conversation is permanently deleted
        $this->assertDatabaseMissing('conversations', ['id' => $conversationId]);
        $this->assertNull(Conversation::withTrashed()->find($conversationId));
    }
}

