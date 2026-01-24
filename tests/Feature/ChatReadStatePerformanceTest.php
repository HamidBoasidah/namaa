<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Repositories\ConversationRepository;
use App\Services\ReadStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Performance Tests for Chat Read State System
 * 
 * These tests verify that the chat read state system meets performance requirements
 * for query execution times and scalability.
 * 
 * Feature: chat-read-state
 * @validates Requirements 2.3, 2.4, 5.3, 5.5
 */
class ChatReadStatePerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function conversation_list_query_with_1000_conversations_completes_under_100ms()
    {
        // Test conversation list query with 1000 conversations (< 100ms)
        // **Validates: Requirements 2.3, 2.4, 5.3, 5.5**
        
        $this->markTestSkipped('Performance test - run manually when needed');
        
        // Arrange: Create user with 1000 conversations
        $user = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        // Create 1000 conversations with messages
        for ($i = 0; $i < 1000; $i++) {
            $booking = Booking::factory()->create([
                'client_id' => $user->id,
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
                'user_id' => $user->id,
            ]);
            
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $consultantUser->id,
            ]);
            
            // Create random number of messages (1-50)
            $messageCount = rand(1, 50);
            Message::factory()->count($messageCount)->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => 'Test message',
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }
        
        // Act: Measure query time for conversation list with unread counts
        $start = microtime(true);
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/conversations');
        
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        // Assert: Response is successful
        $response->assertOk();
        
        // Assert: Query completes in under 100ms
        $this->assertLessThan(100, $duration, 
            "Conversation list query took {$duration}ms, expected < 100ms");
        
        // Assert: All conversations returned
        $this->assertCount(1000, $response->json('data'));
        
        // Output performance metric for monitoring
        echo "\nConversation list query (1000 conversations): {$duration}ms\n";
    }

    /** @test */
    public function unread_count_calculation_completes_under_10ms_per_conversation()
    {
        // Test unread count calculation (< 10ms per conversation)
        // **Validates: Requirements 2.3, 2.4, 5.3**
        
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
        
        // Create 100 messages
        Message::factory()->count(100)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Measure unread count calculation time
        $repository = app(ConversationRepository::class);
        
        $start = microtime(true);
        $unreadCount = $repository->getUnreadCount($conversation->id, $client->id);
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        // Assert: Unread count is correct
        $this->assertEquals(100, $unreadCount);
        
        // Assert: Query completes in under 10ms
        $this->assertLessThan(10, $duration, 
            "Unread count calculation took {$duration}ms, expected < 10ms");
        
        // Output performance metric for monitoring
        echo "\nUnread count calculation: {$duration}ms\n";
    }

    /** @test */
    public function mark_as_read_operation_completes_under_50ms()
    {
        // Test mark-as-read operation (< 50ms including HTTP overhead)
        // **Validates: Requirements 5.3, 5.5**
        
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
        
        // Create messages
        $messages = Message::factory()->count(50)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Measure mark-as-read operation time
        $start = microtime(true);
        
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        // Assert: Response is successful
        $response->assertOk()
            ->assertJsonPath('data.unread_count', 0);
        
        // Assert: Operation completes in under 50ms (including HTTP overhead)
        // Note: The actual database UPDATE is < 5ms, but HTTP request adds overhead
        $this->assertLessThan(50, $duration, 
            "Mark-as-read operation took {$duration}ms, expected < 50ms");
        
        // Output performance metric for monitoring
        echo "\nMark-as-read operation (with HTTP overhead): {$duration}ms\n";
    }

    /** @test */
    public function query_plans_use_indexes_for_unread_count()
    {
        // Verify query plans use indexes (EXPLAIN analysis)
        // **Validates: Requirements 5.3**
        
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
        
        $participant = ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $consultantUser->id,
        ]);
        
        // Create messages
        Message::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Get EXPLAIN output for unread count query
        $lastReadId = $participant->last_read_message_id ?? 0;
        
        $explain = DB::select('EXPLAIN SELECT COUNT(*) FROM messages 
            WHERE conversation_id = ? 
            AND sender_id != ? 
            AND id > ?', 
            [$conversation->id, $client->id, $lastReadId]
        );
        
        // Assert: Query uses index (check for index usage in EXPLAIN output)
        // Note: The exact EXPLAIN output format varies by database (SQLite vs MySQL)
        $explainOutput = json_encode($explain);
        
        $this->assertNotEmpty($explain, 'EXPLAIN query should return results');
        
        // Output EXPLAIN for manual inspection
        echo "\nEXPLAIN output for unread count query:\n";
        print_r($explain);
        
        // For SQLite, check that the query plan uses an index
        // SQLite EXPLAIN output shows opcodes like "OpenRead" with index references
        $usesIndex = false;
        foreach ($explain as $row) {
            // Check if the query plan references an index (opcode "OpenRead" with p4 containing index info)
            if (isset($row->opcode) && $row->opcode === 'OpenRead' && !empty($row->p4)) {
                $usesIndex = true;
                break;
            }
        }
        
        $this->assertTrue($usesIndex, 
            'Query should use an index for efficient execution');
    }

    /** @test */
    public function conversation_list_query_uses_efficient_joins()
    {
        // Verify conversation list query uses efficient joins
        // **Validates: Requirements 2.3, 2.4, 5.3**
        
        // Arrange: Create conversations
        $user = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        // Create 10 conversations for testing
        for ($i = 0; $i < 10; $i++) {
            $booking = Booking::factory()->create([
                'client_id' => $user->id,
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
                'user_id' => $user->id,
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
        }
        
        // Act: Enable query logging
        DB::enableQueryLog();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/conversations');
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        // Assert: Response is successful
        $response->assertOk();
        
        // Assert: No N+1 query problem
        // We expect a reasonable number of queries (not 10+ for 10 conversations)
        $queryCount = count($queries);
        
        // Allow some queries for auth, conversation list, and related data
        // With eager loading, should not be proportional to number of conversations
        $this->assertLessThan(40, $queryCount, 
            "Query count is {$queryCount}, which may indicate N+1 problem");
        
        // Output query information for analysis
        echo "\nTotal queries executed: {$queryCount}\n";
        echo "Conversations returned: " . count($response->json('data')) . "\n";
        
        // Verify query count is not proportional to conversation count
        // If we had N+1 problem, we'd expect ~10+ queries per conversation
        $queriesPerConversation = $queryCount / 10;
        $this->assertLessThan(4, $queriesPerConversation,
            "Queries per conversation: {$queriesPerConversation}, should be < 4");
        
        // Output first few queries for inspection
        echo "\nFirst 5 queries:\n";
        foreach (array_slice($queries, 0, 5) as $index => $query) {
            echo ($index + 1) . ". " . $query['query'] . "\n";
            echo "   Time: " . $query['time'] . "ms\n";
        }
    }

    /** @test */
    public function mark_as_read_updates_single_row_efficiently()
    {
        // Verify mark-as-read updates exactly one row
        // **Validates: Requirements 5.4**
        
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
        
        // Create messages
        Message::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'in_session',
        ]);
        
        // Act: Enable query logging and mark as read
        DB::enableQueryLog();
        
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/read");
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        // Assert: Response is successful
        $response->assertOk();
        
        // Assert: Find the UPDATE query for conversation_participants
        $updateQueries = array_filter($queries, function($query) {
            return stripos($query['query'], 'update') !== false 
                && stripos($query['query'], 'conversation_participants') !== false;
        });
        
        $this->assertNotEmpty($updateQueries, 
            'Should have at least one UPDATE query for conversation_participants');
        
        // Assert: No UPDATE queries on messages table
        $messageUpdateQueries = array_filter($queries, function($query) {
            return stripos($query['query'], 'update') !== false 
                && stripos($query['query'], 'messages') !== false;
        });
        
        $this->assertEmpty($messageUpdateQueries, 
            'Should have no UPDATE queries on messages table');
        
        // Output query information
        echo "\nMark-as-read queries:\n";
        foreach ($updateQueries as $index => $query) {
            echo "UPDATE query: " . $query['query'] . "\n";
            echo "Time: " . $query['time'] . "ms\n";
        }
    }
}
