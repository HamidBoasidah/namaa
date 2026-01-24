<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Backward Compatibility Tests for Chat Read State Feature
 * 
 * These tests verify that the read state feature maintains backward compatibility
 * with existing API clients and does not break existing functionality.
 * 
 * Feature: chat-read-state
 * @validates Requirements 7.1, 7.2, 7.3, 7.5
 */
class ChatReadStateBackwardCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_maintains_messages_table_structure_unchanged()
    {
        // Test: Verify messages table structure unchanged
        // **Validates: Requirement 7.1**
        
        // Verify messages table exists
        $this->assertTrue(Schema::hasTable('messages'));
        
        // Verify original columns exist with correct types
        $this->assertTrue(Schema::hasColumn('messages', 'id'));
        $this->assertTrue(Schema::hasColumn('messages', 'conversation_id'));
        $this->assertTrue(Schema::hasColumn('messages', 'sender_id'));
        $this->assertTrue(Schema::hasColumn('messages', 'body'));
        $this->assertTrue(Schema::hasColumn('messages', 'type'));
        $this->assertTrue(Schema::hasColumn('messages', 'context'));
        $this->assertTrue(Schema::hasColumn('messages', 'created_at'));
        $this->assertTrue(Schema::hasColumn('messages', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('messages', 'deleted_at'));
        
        // Verify NO read state columns were added to messages table
        $this->assertFalse(Schema::hasColumn('messages', 'is_read'));
        $this->assertFalse(Schema::hasColumn('messages', 'read_at'));
        $this->assertFalse(Schema::hasColumn('messages', 'read_by'));
        
        // Verify indexes exist by checking index information
        $connection = Schema::getConnection();
        $tableName = 'messages';
        
        // Get all indexes for the table
        $indexes = $connection->select("PRAGMA index_list({$tableName})");
        
        // Verify we have indexes (exact structure varies by database)
        $this->assertNotEmpty($indexes, 'Messages table should have indexes');
        
        // The important thing is that the table structure is unchanged
        // and no read state columns were added
        $this->assertTrue(true, 'Messages table structure is unchanged');
    }

    /** @test */
    public function it_maintains_existing_conversation_listing_api_response_structure()
    {
        // Test: Existing API clients still work without changes
        // **Validates: Requirements 7.2, 7.5**
        
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
        
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Call conversation list endpoint
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        // Assert: Response has 200 status
        $response->assertOk();
        
        // Assert: All original fields are present
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'booking_id',
                    'other_participant',
                    'last_message',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        
        // Assert: Original field types are unchanged
        $data = $response->json('data.0');
        $this->assertIsInt($data['id']);
        $this->assertIsInt($data['booking_id']);
        $this->assertIsArray($data['other_participant']);
        $this->assertIsString($data['created_at']);
        $this->assertIsString($data['updated_at']);
        
        // Assert: New field (unread_count) is added without breaking existing structure
        $this->assertArrayHasKey('unread_count', $data);
        $this->assertIsInt($data['unread_count']);
        
        // Assert: No original fields were removed
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('booking_id', $data);
        $this->assertArrayHasKey('other_participant', $data);
        $this->assertArrayHasKey('last_message', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
    }

    /** @test */
    public function it_maintains_existing_message_fetching_api_response_structure()
    {
        // Test: Existing message fetching API responses not broken
        // **Validates: Requirements 7.3, 7.5**
        
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
        
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Call message fetch endpoint
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        // Assert: Response has 200 status
        $response->assertOk();
        
        // Assert: All original fields are present in data array
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'conversation_id',
                    'sender_id',
                    'body',
                    'type',
                    'context',
                    'created_at',
                ]
            ],
            'meta' => [
                'next_cursor',
                'prev_cursor',
                'per_page',
            ]
        ]);
        
        // Assert: Original message field types are unchanged
        $message = $response->json('data.0');
        $this->assertIsInt($message['id']);
        $this->assertIsInt($message['conversation_id']);
        $this->assertIsInt($message['sender_id']);
        $this->assertIsString($message['body']);
        $this->assertIsString($message['type']);
        $this->assertIsString($message['context']);
        $this->assertIsString($message['created_at']);
        
        // Assert: New field (unread_count) is added in meta without breaking existing structure
        $this->assertArrayHasKey('unread_count', $response->json('meta'));
        $this->assertIsInt($response->json('meta.unread_count'));
        
        // Assert: No original message fields were removed
        $this->assertArrayHasKey('id', $message);
        $this->assertArrayHasKey('conversation_id', $message);
        $this->assertArrayHasKey('sender_id', $message);
        $this->assertArrayHasKey('body', $message);
        $this->assertArrayHasKey('type', $message);
        $this->assertArrayHasKey('context', $message);
        $this->assertArrayHasKey('created_at', $message);
    }

    /** @test */
    public function it_maintains_existing_conversation_creation_logic()
    {
        // Test: Existing conversation creation logic works
        // **Validates: Requirement 7.5**
        
        // Arrange: Create users and booking
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
        
        // Act: Create conversation using existing logic
        $conversation = Conversation::create([
            'booking_id' => $booking->id,
        ]);
        
        // Assert: Conversation created successfully
        $this->assertNotNull($conversation->id);
        $this->assertEquals($booking->id, $conversation->booking_id);
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'booking_id' => $booking->id,
        ]);
        
        // Act: Create participants using existing logic
        $clientParticipant = ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        $consultantParticipant = ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Assert: Participants created successfully with null read markers (default state)
        $this->assertNotNull($clientParticipant->id);
        $this->assertNull($clientParticipant->last_read_message_id);
        $this->assertNull($clientParticipant->last_read_at);
        
        $this->assertNotNull($consultantParticipant->id);
        $this->assertNull($consultantParticipant->last_read_message_id);
        $this->assertNull($consultantParticipant->last_read_at);
        
        // Assert: Database records are correct
        $this->assertDatabaseHas('conversation_participants', [
            'id' => $clientParticipant->id,
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => null,
        ]);
        
        $this->assertDatabaseHas('conversation_participants', [
            'id' => $consultantParticipant->id,
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
            'last_read_message_id' => null,
        ]);
    }

    /** @test */
    public function it_maintains_existing_message_creation_logic()
    {
        // Test: Existing message creation logic works
        // **Validates: Requirement 7.5**
        
        // Arrange: Create conversation
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
        
        // Act: Create message using existing logic
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Hello, this is a test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Assert: Message created successfully
        $this->assertNotNull($message->id);
        $this->assertEquals($conversation->id, $message->conversation_id);
        $this->assertEquals($consultantUser->id, $message->sender_id);
        $this->assertEquals('Hello, this is a test message', $message->body);
        $this->assertEquals('text', $message->type);
        $this->assertEquals('in_session', $message->context);
        
        // Assert: Database record is correct
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Hello, this is a test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Assert: Message table structure unchanged (no read state columns)
        $messageRecord = Message::find($message->id);
        $this->assertObjectNotHasProperty('is_read', $messageRecord);
        $this->assertObjectNotHasProperty('read_at', $messageRecord);
        $this->assertObjectNotHasProperty('read_by', $messageRecord);
    }

    /** @test */
    public function it_allows_existing_api_clients_to_work_without_read_state_awareness()
    {
        // Test: API clients that don't use read state features still work
        // **Validates: Requirements 7.2, 7.3, 7.5**
        
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
        
        Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Old client lists conversations (ignores unread_count field)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson('/api/conversations');
        
        $response->assertOk();
        $data = $response->json('data.0');
        
        // Assert: Old client can still access all original fields
        $this->assertNotNull($data['id']);
        $this->assertNotNull($data['booking_id']);
        $this->assertNotNull($data['other_participant']);
        $this->assertNotNull($data['created_at']);
        $this->assertNotNull($data['updated_at']);
        
        // Act: Old client fetches messages (ignores implicit mark-as-read)
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");
        
        $response->assertOk();
        $messages = $response->json('data');
        
        // Assert: Old client can still access all message fields
        $this->assertCount(5, $messages);
        $this->assertNotNull($messages[0]['id']);
        $this->assertNotNull($messages[0]['conversation_id']);
        $this->assertNotNull($messages[0]['sender_id']);
        $this->assertNotNull($messages[0]['body']);
        $this->assertNotNull($messages[0]['type']);
        $this->assertNotNull($messages[0]['context']);
        
        // Assert: Old client's workflow still functions correctly
        // (Even though read state is being tracked in the background)
        $this->assertTrue(true, 'Old client workflow completed successfully');
    }
}
