<?php

namespace Tests\Unit\Migrations;

use App\Models\Consultant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration integrity tests for Chat Read State System
 * 
 * Tests that the database schema changes for the chat read state feature
 * are correctly applied, including column types, foreign keys, indexes, and nullable constraints.
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 5.1, 5.2**
 */
class ChatReadStateMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method to create a minimal conversation for testing
     */
    private function createTestConversation(): int
    {
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();
        
        // Create a minimal booking
        $bookingId = DB::table('bookings')->insertGetId([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'duration_minutes' => 60,
            'buffer_after_minutes' => 0,
            'status' => 'confirmed',
            'consultation_method' => 'video',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create conversation
        return DB::table('conversations')->insertGetId([
            'booking_id' => $bookingId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test that last_read_message_id column exists in conversation_participants table
     * 
     * **Validates: Requirement 1.1, 1.2**
     */
    public function test_conversation_participants_has_last_read_message_id_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('conversation_participants', 'last_read_message_id'),
            'conversation_participants table should have last_read_message_id column'
        );
    }

    /**
     * Test that last_read_at column exists in conversation_participants table
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_conversation_participants_has_last_read_at_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('conversation_participants', 'last_read_at'),
            'conversation_participants table should have last_read_at column'
        );
    }

    /**
     * Test that last_read_message_id is nullable
     * 
     * **Validates: Requirement 1.5**
     */
    public function test_last_read_message_id_is_nullable(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();

        // Create a participant without last_read_message_id
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => null,
            'last_read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNull($participant->last_read_message_id);
    }

    /**
     * Test that last_read_at is nullable
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_last_read_at_is_nullable(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();

        // Create a participant without last_read_at
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => null,
            'last_read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNull($participant->last_read_at);
    }

    /**
     * Test that last_read_message_id can store valid message IDs
     * 
     * **Validates: Requirement 1.2**
     */
    public function test_last_read_message_id_stores_valid_message_ids(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();
        $sender = User::factory()->create();

        // Create message directly
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $sender->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a participant with last_read_message_id
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => $messageId,
            'last_read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertEquals($messageId, $participant->last_read_message_id);
    }

    /**
     * Test that last_read_at can store timestamp values
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_last_read_at_stores_timestamp_values(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();
        $timestamp = now();

        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => null,
            'last_read_at' => $timestamp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($participant->last_read_at);
        $this->assertEquals(
            $timestamp->format('Y-m-d H:i:s'),
            $participant->last_read_at
        );
    }

    /**
     * Test that foreign key constraint on last_read_message_id works correctly
     * 
     * **Validates: Requirement 1.2**
     */
    public function test_last_read_message_id_foreign_key_constraint(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();
        $sender = User::factory()->create();

        // Create message directly
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $sender->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a participant with valid last_read_message_id
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => $messageId,
            'last_read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertEquals($messageId, $participant->last_read_message_id);
    }

    /**
     * Test that deleting a message sets last_read_message_id to null (onDelete set null)
     * 
     * **Validates: Requirement 1.2**
     */
    public function test_deleting_message_sets_last_read_message_id_to_null(): void
    {
        $conversationId = $this->createTestConversation();
        $user = User::factory()->create();
        $sender = User::factory()->create();

        // Create message directly
        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $sender->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a participant with last_read_message_id pointing to the message
        DB::table('conversation_participants')->insert([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
            'last_read_message_id' => $messageId,
            'last_read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete the message (hard delete)
        DB::table('messages')->where('id', $messageId)->delete();

        // Verify last_read_message_id is set to null
        $participant = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNull($participant->last_read_message_id);
    }

    /**
     * Test that composite index exists on messages table for (conversation_id, id)
     * 
     * **Validates: Requirement 5.1**
     */
    public function test_messages_has_composite_index_on_conversation_id_and_id(): void
    {
        $indexes = Schema::getIndexes('messages');
        
        $hasCompositeIndex = false;
        foreach ($indexes as $index) {
            if ($index['name'] === 'idx_messages_conversation_id') {
                $hasCompositeIndex = true;
                $this->assertContains('conversation_id', $index['columns']);
                $this->assertContains('id', $index['columns']);
                break;
            }
        }
        
        $this->assertTrue(
            $hasCompositeIndex,
            'messages table should have composite index idx_messages_conversation_id on (conversation_id, id)'
        );
    }

    /**
     * Test that user_id index exists on conversation_participants table
     * 
     * **Validates: Requirement 5.2**
     */
    public function test_conversation_participants_has_user_id_index(): void
    {
        $indexes = Schema::getIndexes('conversation_participants');
        
        $hasUserIdIndex = false;
        foreach ($indexes as $index) {
            if (in_array('user_id', $index['columns'])) {
                $hasUserIdIndex = true;
                break;
            }
        }
        
        $this->assertTrue(
            $hasUserIdIndex,
            'conversation_participants table should have index on user_id'
        );
    }

    /**
     * Test that conversation_id index exists on conversation_participants table
     * 
     * **Validates: Requirement 5.2**
     */
    public function test_conversation_participants_has_conversation_id_index(): void
    {
        $indexes = Schema::getIndexes('conversation_participants');
        
        $hasConversationIdIndex = false;
        foreach ($indexes as $index) {
            if (in_array('conversation_id', $index['columns'])) {
                $hasConversationIdIndex = true;
                break;
            }
        }
        
        $this->assertTrue(
            $hasConversationIdIndex,
            'conversation_participants table should have index on conversation_id'
        );
    }
}
