<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Tests for Chat Read State API Endpoints
 * 
 * These tests verify specific examples and API contracts for read state functionality.
 * 
 * Feature: chat-read-state
 * @validates Requirements 4.1, 4.2, 4.3, 4.4
 */
class ReadStateApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_conversations_returns_200_with_unread_counts()
    {
        // Example 2: Test GET /api/conversations returns 200 with unread counts
        // **Validates: Requirements 4.1**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create 3 unread messages from consultant
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'status_code',
                'data' => [
                    '*' => [
                        'id',
                        'booking_id',
                        'other_participant',
                        'last_message',
                        'unread_count',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJsonPath('data.0.unread_count', 3);
    }

    /** @test */
    public function get_messages_returns_200_with_messages_and_unread_count()
    {
        // Example 2: Test GET /api/conversations/{id}/messages returns 200 with messages and unread count
        // **Validates: Requirements 4.2**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create messages
        Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'status_code',
                'data' => [
                    '*' => [
                        'id',
                        'conversation_id',
                        'sender_id',
                        'body',
                        'type',
                    ]
                ],
                'meta' => [
                    'next_cursor',
                    'prev_cursor',
                    'per_page',
                    'unread_count',
                ]
            ])
            ->assertJsonPath('meta.unread_count', 0); // Should be 0 after fetching messages (implicit mark as read)
    }

    /** @test */
    public function post_mark_as_read_returns_200_with_success_and_unread_count()
    {
        // Example 2: Test POST /api/conversations/{id}/read returns 200 with success and unread count
        // **Validates: Requirements 4.3**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create messages
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'status_code',
                'data' => [
                    'unread_count',
                ]
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 0); // Should be 0 after marking as read
    }

    /** @test */
    public function non_participant_receives_403_forbidden()
    {
        // Example 2: Test non-participant receives 403 Forbidden
        // **Validates: Requirements 4.4**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $nonParticipant = User::factory()->create(['user_type' => 'customer']);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Test GET messages - non-participant should get 403
        $response = $this->actingAs($nonParticipant, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertForbidden();
        
        // Test POST mark as read - non-participant should get 403
        $response = $this->actingAs($nonParticipant, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertForbidden();
    }

    /** @test */
    public function get_messages_implicitly_marks_conversation_as_read()
    {
        // Test that fetching messages automatically marks conversation as read
        // **Validates: Requirements 4.2**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create messages
        $messages = Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Verify unread count is 3 before fetching messages
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 3);
        
        // Fetch messages (should implicitly mark as read)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertOk()
            ->assertJsonPath('meta.unread_count', 0);
        
        // Verify unread count is now 0 in conversation list
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 0);
        
        // Verify last_read_message_id was updated in database
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($messages->last()->id, $participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }

    /** @test */
    public function mark_as_read_with_explicit_message_id()
    {
        // Test marking as read with explicit message ID
        // **Validates: Requirements 4.3**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create messages
        $messages = Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Mark as read up to the 3rd message
        $thirdMessage = $messages[2];
        
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read", [
                'message_id' => $thirdMessage->id,
            ]);
        
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 2); // 2 messages remain unread
        
        // Verify last_read_message_id was updated correctly
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($thirdMessage->id, $participant->last_read_message_id);
    }

    /** @test */
    public function mark_as_read_on_empty_conversation_returns_success()
    {
        // Test marking as read on conversation with no messages
        // **Validates: Requirements 4.3**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // No messages created
        
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 0);
    }

    /** @test */
    public function unread_count_excludes_own_messages()
    {
        // Test that unread count only includes messages from other participants
        // **Validates: Requirements 4.1**
        
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create 3 messages from consultant
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'From consultant',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Create 2 messages from client (own messages)
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'body' => 'From client',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Client should only see 3 unread messages (from consultant)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 3);
    }
}
