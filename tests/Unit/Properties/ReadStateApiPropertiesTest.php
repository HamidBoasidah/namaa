<?php

namespace Tests\Unit\Properties;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Tests for Chat Read State API Correctness Properties
 * 
 * These tests verify universal properties that should hold across all valid API executions.
 * Each test runs multiple iterations with randomly generated data.
 * 
 * Feature: chat-read-state
 * @validates Requirements 4.4, 4.5, 7.2, 7.3, 7.4
 */
class ReadStateApiPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected int $iterations = 100; // Minimum 100 iterations as per spec requirements

    // ─────────────────────────────────────────────────────────────
    // Property 5: Authorization Enforcement
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_authorization_enforcement()
    {
        // Feature: chat-read-state, Property 5: Authorization Enforcement
        // For any user who is not a participant in a conversation, API requests to
        // view messages, get unread counts, or mark the conversation as read should
        // be rejected with a 403 Forbidden response. For any user who is a participant,
        // the same requests should succeed with a 200 OK response.
        // **Validates: Requirements 4.4**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create participants
            $client = User::factory()->create(['user_type' => 'customer']);
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            
            // Create non-participant
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
            
            // Create some messages
            Message::factory()->count(rand(1, 5))->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => 'Test message',
                'type' => 'text',
                'context' => 'in_session',
            ]);
            
            // Test 1: GET /api/conversations/{id}/messages - Non-participant should get 403
            $response = $this->actingAs($nonParticipant, 'sanctum')
                ->getJson("/api/conversations/{$conversation->id}/messages");
            
            $this->assertEquals(403, $response->status(),
                "Iteration {$i}: Non-participant should get 403 for GET messages");
            
            // Test 2: GET /api/conversations/{id}/messages - Participant should get 200
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations/{$conversation->id}/messages");
            
            $this->assertEquals(200, $response->status(),
                "Iteration {$i}: Participant should get 200 for GET messages");
            
            // Test 3: POST /api/conversations/{id}/read - Non-participant should get 403
            $response = $this->actingAs($nonParticipant, 'sanctum')
                ->postJson("/api/conversations/{$conversation->id}/read");
            
            $this->assertEquals(403, $response->status(),
                "Iteration {$i}: Non-participant should get 403 for POST mark as read");
            
            // Test 4: POST /api/conversations/{id}/read - Participant should get 200
            $response = $this->actingAs($client, 'sanctum')
                ->postJson("/api/conversations/{$conversation->id}/read");
            
            $this->assertEquals(200, $response->status(),
                "Iteration {$i}: Participant should get 200 for POST mark as read");
            
            // Test 5: GET /api/conversations - Should only return conversations where user is participant
            $response = $this->actingAs($nonParticipant, 'sanctum')
                ->getJson("/api/conversations");
            
            $this->assertEquals(200, $response->status(),
                "Iteration {$i}: GET conversations should return 200 for any authenticated user");
            
            $conversations = $response->json('data');
            $conversationIds = collect($conversations)->pluck('id')->toArray();
            
            $this->assertNotContains($conversation->id, $conversationIds,
                "Iteration {$i}: Non-participant should not see conversation in their list");
            
            // Test 6: Participant should see conversation in their list
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations");
            
            $this->assertEquals(200, $response->status(),
                "Iteration {$i}: Participant should get 200 for GET conversations");
            
            $conversations = $response->json('data');
            $conversationIds = collect($conversations)->pluck('id')->toArray();
            
            $this->assertContains($conversation->id, $conversationIds,
                "Iteration {$i}: Participant should see conversation in their list");
            
            // Cleanup
            $booking->delete();
            $nonParticipant->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 6: Unread Count Response Type
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_unread_count_response_type()
    {
        // Feature: chat-read-state, Property 6: Unread Count Response Type
        // For any API response that includes unread counts (conversation list, message fetch,
        // mark as read), the unread_count field should be an integer value greater than or
        // equal to zero.
        // **Validates: Requirements 4.5**
        
        for ($i = 0; $i < $this->iterations; $i++) {
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
            
            // Create random number of messages
            $messageCount = rand(0, 20);
            for ($j = 0; $j < $messageCount; $j++) {
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $consultantUser->id,
                    'body' => "Message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
            }
            
            // Test 1: GET /api/conversations - unread_count should be integer >= 0
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations");
            
            $response->assertOk();
            
            $conversations = $response->json('data');
            foreach ($conversations as $conv) {
                $this->assertArrayHasKey('unread_count', $conv,
                    "Iteration {$i}: Conversation list should include unread_count field");
                
                $this->assertIsInt($conv['unread_count'],
                    "Iteration {$i}: unread_count should be integer, got " . gettype($conv['unread_count']));
                
                $this->assertGreaterThanOrEqual(0, $conv['unread_count'],
                    "Iteration {$i}: unread_count should be >= 0, got {$conv['unread_count']}");
            }
            
            // Test 2: GET /api/conversations/{id}/messages - unread_count should be integer >= 0
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations/{$conversation->id}/messages");
            
            $response->assertOk();
            
            $meta = $response->json('meta');
            $this->assertArrayHasKey('unread_count', $meta,
                "Iteration {$i}: Message fetch response should include unread_count in meta");
            
            $this->assertIsInt($meta['unread_count'],
                "Iteration {$i}: unread_count in meta should be integer, got " . gettype($meta['unread_count']));
            
            $this->assertGreaterThanOrEqual(0, $meta['unread_count'],
                "Iteration {$i}: unread_count in meta should be >= 0, got {$meta['unread_count']}");
            
            // Test 3: POST /api/conversations/{id}/read - unread_count should be integer >= 0
            $response = $this->actingAs($client, 'sanctum')
                ->postJson("/api/conversations/{$conversation->id}/read");
            
            $response->assertOk();
            
            $data = $response->json('data');
            $this->assertArrayHasKey('unread_count', $data,
                "Iteration {$i}: Mark as read response should include unread_count in data");
            
            $this->assertIsInt($data['unread_count'],
                "Iteration {$i}: unread_count in data should be integer, got " . gettype($data['unread_count']));
            
            $this->assertGreaterThanOrEqual(0, $data['unread_count'],
                "Iteration {$i}: unread_count in data should be >= 0, got {$data['unread_count']}");
            
            // After marking as read, unread_count should be 0
            $this->assertEquals(0, $data['unread_count'],
                "Iteration {$i}: unread_count should be 0 after marking all messages as read");
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 8: API Backward Compatibility
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_api_backward_compatibility()
    {
        // Feature: chat-read-state, Property 8: API Backward Compatibility
        // For any existing API endpoint that is enhanced with read state functionality
        // (GET /api/conversations, GET /api/conversations/{id}/messages), all original
        // response fields should remain present with the same data types and structure,
        // with only new fields added.
        // **Validates: Requirements 7.2, 7.3, 7.4**
        
        for ($i = 0; $i < $this->iterations; $i++) {
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
            
            // Create some messages
            Message::factory()->count(rand(1, 5))->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => 'Test message',
                'type' => 'text',
                'context' => 'in_session',
            ]);
            
            // Test 1: GET /api/conversations - Verify all original fields present
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations");
            
            $response->assertOk();
            
            $conversations = $response->json('data');
            $this->assertNotEmpty($conversations,
                "Iteration {$i}: Should have at least one conversation");
            
            foreach ($conversations as $conv) {
                // Original fields that must be present
                $this->assertArrayHasKey('id', $conv,
                    "Iteration {$i}: Original field 'id' must be present");
                $this->assertArrayHasKey('booking_id', $conv,
                    "Iteration {$i}: Original field 'booking_id' must be present");
                $this->assertArrayHasKey('other_participant', $conv,
                    "Iteration {$i}: Original field 'other_participant' must be present");
                $this->assertArrayHasKey('last_message', $conv,
                    "Iteration {$i}: Original field 'last_message' must be present");
                $this->assertArrayHasKey('created_at', $conv,
                    "Iteration {$i}: Original field 'created_at' must be present");
                $this->assertArrayHasKey('updated_at', $conv,
                    "Iteration {$i}: Original field 'updated_at' must be present");
                
                // New field added
                $this->assertArrayHasKey('unread_count', $conv,
                    "Iteration {$i}: New field 'unread_count' should be present");
                
                // Verify field types
                $this->assertIsInt($conv['id'],
                    "Iteration {$i}: Field 'id' should be integer");
                $this->assertIsInt($conv['booking_id'],
                    "Iteration {$i}: Field 'booking_id' should be integer");
                $this->assertIsArray($conv['other_participant'],
                    "Iteration {$i}: Field 'other_participant' should be array");
                $this->assertIsString($conv['created_at'],
                    "Iteration {$i}: Field 'created_at' should be string");
                $this->assertIsString($conv['updated_at'],
                    "Iteration {$i}: Field 'updated_at' should be string");
                $this->assertIsInt($conv['unread_count'],
                    "Iteration {$i}: Field 'unread_count' should be integer");
            }
            
            // Test 2: GET /api/conversations/{id}/messages - Verify all original fields present
            $response = $this->actingAs($client, 'sanctum')
                ->getJson("/api/conversations/{$conversation->id}/messages");
            
            $response->assertOk();
            
            // Original response structure
            $this->assertArrayHasKey('success', $response->json(),
                "Iteration {$i}: Original field 'success' must be present");
            $this->assertArrayHasKey('message', $response->json(),
                "Iteration {$i}: Original field 'message' must be present");
            $this->assertArrayHasKey('status_code', $response->json(),
                "Iteration {$i}: Original field 'status_code' must be present");
            $this->assertArrayHasKey('data', $response->json(),
                "Iteration {$i}: Original field 'data' must be present");
            $this->assertArrayHasKey('meta', $response->json(),
                "Iteration {$i}: Original field 'meta' must be present");
            
            // Original meta fields
            $meta = $response->json('meta');
            $this->assertArrayHasKey('next_cursor', $meta,
                "Iteration {$i}: Original meta field 'next_cursor' must be present");
            $this->assertArrayHasKey('prev_cursor', $meta,
                "Iteration {$i}: Original meta field 'prev_cursor' must be present");
            $this->assertArrayHasKey('per_page', $meta,
                "Iteration {$i}: Original meta field 'per_page' must be present");
            
            // New meta field added
            $this->assertArrayHasKey('unread_count', $meta,
                "Iteration {$i}: New meta field 'unread_count' should be present");
            
            // Verify message structure unchanged
            $messages = $response->json('data');
            if (!empty($messages)) {
                $message = $messages[0];
                $this->assertArrayHasKey('id', $message,
                    "Iteration {$i}: Message field 'id' must be present");
                $this->assertArrayHasKey('conversation_id', $message,
                    "Iteration {$i}: Message field 'conversation_id' must be present");
                $this->assertArrayHasKey('sender_id', $message,
                    "Iteration {$i}: Message field 'sender_id' must be present");
                $this->assertArrayHasKey('body', $message,
                    "Iteration {$i}: Message field 'body' must be present");
                $this->assertArrayHasKey('type', $message,
                    "Iteration {$i}: Message field 'type' must be present");
            }
            
            // Cleanup
            $booking->delete();
        }
    }
}
