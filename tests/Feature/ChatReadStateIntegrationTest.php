<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Tests for Chat Read State Complete Flows
 * 
 * These tests verify the end-to-end functionality of the chat read state system,
 * testing complete flows from conversation creation to read state tracking.
 * 
 * Feature: chat-read-state
 * @validates Requirements 3.3, 3.5, 7.5
 */
class ChatReadStateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_tracks_read_state_through_complete_conversation_flow()
    {
        // Test: Create conversation → Send messages → Mark as read → Verify unread count
        // **Validates: Requirements 3.3, 7.5**
        
        // Step 1: Create conversation with two participants
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
        
        $clientParticipant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        $consultantParticipant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Verify: Initial state - no messages, no read markers
        $this->assertNull($clientParticipant->last_read_message_id);
        $this->assertNull($consultantParticipant->last_read_message_id);
        
        // Step 2: Consultant sends 3 messages
        $message1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Hello, how can I help you?',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'I have availability tomorrow',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $message3 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Let me know if that works',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Step 3: Client checks conversation list - should see 3 unread messages
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 3);
        
        // Step 4: Client sends a reply
        $message4 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'body' => 'Tomorrow works great!',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Step 5: Consultant checks conversation list - should see 1 unread message
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
        
        // Step 6: Client marks conversation as read
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
        
        // Verify: Client's read marker updated to latest message
        $clientParticipant->refresh();
        $this->assertEquals($message4->id, $clientParticipant->last_read_message_id);
        $this->assertNotNull($clientParticipant->last_read_at);
        
        // Step 7: Client checks conversation list again - should see 0 unread
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 0);
        
        // Step 8: Consultant still has 1 unread message (independent read state)
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
        
        // Step 9: Consultant marks as read
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
        
        // Verify: Both participants have read all messages
        $clientParticipant->refresh();
        $consultantParticipant->refresh();
        $this->assertEquals($message4->id, $clientParticipant->last_read_message_id);
        $this->assertEquals($message4->id, $consultantParticipant->last_read_message_id);
    }

    /** @test */
    public function it_implicitly_marks_as_read_when_fetching_messages()
    {
        // Test: Fetch messages implicitly marks as read
        // **Validates: Requirements 3.3, 7.5**
        
        // Arrange: Create conversation with messages
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
        
        // Create 5 messages from consultant
        $messages = Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Verify: Client has 5 unread messages before fetching
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 5);
        
        // Act: Client fetches messages (should implicitly mark as read)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertOk()
            ->assertJsonPath('meta.unread_count', 0);
        
        // Verify: Unread count is now 0 in conversation list
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 0);
        
        // Verify: Read marker was updated in database
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($messages->last()->id, $participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }

    /** @test */
    public function it_maintains_independent_read_states_for_multiple_participants()
    {
        // Test: Multiple participants have independent read states
        // **Validates: Requirements 3.5**
        
        // Arrange: Create conversation with two participants
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
        
        // Create messages from both participants
        $message1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Message 1 from consultant',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'body' => 'Message 2 from client',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $message3 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Message 3 from consultant',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Verify: Client has 2 unread messages (from consultant)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 2);
        
        // Verify: Consultant has 1 unread message (from client)
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
        
        // Act: Client marks as read up to message 2
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read", [
                'message_id' => $message2->id,
            ]);
        
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 1); // Still 1 unread (message 3)
        
        // Verify: Client's read marker updated to message 2
        $clientParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($message2->id, $clientParticipant->last_read_message_id);
        
        // Verify: Consultant's read marker unchanged (still null)
        $consultantParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $consultantUser->id)
            ->first();
        
        $this->assertNull($consultantParticipant->last_read_message_id);
        
        // Verify: Consultant still has 1 unread message
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
        
        // Act: Consultant marks all as read
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
        
        // Verify: Consultant's read marker updated to message 3
        $consultantParticipant->refresh();
        $this->assertEquals($message3->id, $consultantParticipant->last_read_message_id);
        
        // Verify: Client still has 1 unread message (independent state)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
    }

    /** @test */
    public function it_handles_race_condition_when_new_message_arrives_during_mark_as_read()
    {
        // Test: Race condition scenario (new message during mark-as-read)
        // **Validates: Requirements 3.3**
        
        // Arrange: Create conversation with messages
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
        
        // Create initial messages
        $message1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Message 1',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Message 2',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Verify: Client has 2 unread messages
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 2);
        
        // Act: Client marks as read up to message 2
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read", [
                'message_id' => $message2->id,
            ]);
        
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
        
        // Simulate race condition: New message arrives after mark-as-read
        $message3 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Message 3 (arrived during mark-as-read)',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Verify: Client's read marker is at message 2 (not affected by new message)
        $clientParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($message2->id, $clientParticipant->last_read_message_id);
        
        // Verify: Client now has 1 unread message (message 3)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 1);
        
        // Verify: No data corruption - all messages exist and are correct
        $this->assertDatabaseHas('messages', ['id' => $message1->id]);
        $this->assertDatabaseHas('messages', ['id' => $message2->id]);
        $this->assertDatabaseHas('messages', ['id' => $message3->id]);
        
        // Verify: Read marker is valid and points to existing message
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => $message2->id,
        ]);
    }
}
