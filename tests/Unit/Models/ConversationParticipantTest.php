<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationParticipantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function new_participant_has_null_last_read_message_id()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Create a new participant
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        // Assert last_read_message_id is null
        $this->assertNull($participant->last_read_message_id);
        $this->assertNull($participant->last_read_at);
    }

    /** @test */
    public function last_read_message_relationship_loads_correctly()
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
            'sender_id' => $consultant->user_id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Create a participant with a read marker
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'last_read_message_id' => $message->id,
            'last_read_at' => now(),
        ]);

        // Load the relationship
        $participant->load('lastReadMessage');

        // Assert the relationship is loaded correctly
        $this->assertNotNull($participant->lastReadMessage);
        $this->assertEquals($message->id, $participant->lastReadMessage->id);
        $this->assertEquals('Test message', $participant->lastReadMessage->body);
    }

    /** @test */
    public function unread_count_accessor_returns_correct_value()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Create multiple messages from the consultant
        $message1 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message 1',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $message2 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message 2',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $message3 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message 3',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Create a participant who has read up to message 1
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'last_read_message_id' => $message1->id,
            'last_read_at' => now(),
        ]);

        // Assert unread count is 2 (message2 and message3)
        $this->assertEquals(2, $participant->unread_count);
    }

    /** @test */
    public function unread_count_is_zero_when_all_messages_are_read()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Create messages
        $message1 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message 1',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $message2 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message 2',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Create a participant who has read all messages
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'last_read_message_id' => $message2->id,
            'last_read_at' => now(),
        ]);

        // Assert unread count is 0
        $this->assertEquals(0, $participant->unread_count);
    }

    /** @test */
    public function unread_count_excludes_own_messages()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Create messages from both users
        $message1 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Message from consultant',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $message2 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'Message from user',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        $message3 = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultant->user_id,
            'body' => 'Another message from consultant',
            'type' => 'text',
            'context' => 'out_of_session',
        ]);

        // Create a participant with null last_read_message_id
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'last_read_message_id' => null,
        ]);

        // Assert unread count is 2 (only messages from consultant)
        $this->assertEquals(2, $participant->unread_count);
    }

    /** @test */
    public function unread_count_is_zero_when_no_messages_exist()
    {
        // Create necessary records
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()
            ->forClient($user)
            ->forConsultant($consultant)
            ->create();
        $conversation = Conversation::create(['booking_id' => $booking->id]);

        // Create a participant with no messages in the conversation
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'last_read_message_id' => null,
        ]);

        // Assert unread count is 0
        $this->assertEquals(0, $participant->unread_count);
    }
}
