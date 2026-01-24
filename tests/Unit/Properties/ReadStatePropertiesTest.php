<?php

namespace Tests\Unit\Properties;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Repositories\ConversationParticipantRepository;
use App\Repositories\ConversationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Property-Based Tests for Chat Read State Correctness Properties
 * 
 * These tests verify universal properties that should hold across all valid executions.
 * Each test runs multiple iterations with randomly generated data.
 * 
 * Feature: chat-read-state
 * @validates Requirements 2.1, 2.2, 3.1, 3.2, 3.5, 5.4
 */
class ReadStatePropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected ConversationRepository $conversationRepo;
    protected ConversationParticipantRepository $participantRepo;
    protected int $iterations = 100; // Minimum 100 iterations as per spec requirements

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationRepo = app(ConversationRepository::class);
        $this->participantRepo = app(ConversationParticipantRepository::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Property 1: Unread Count Accuracy
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_unread_count_accuracy()
    {
        // Feature: chat-read-state, Property 1: Unread Count Accuracy
        // For any conversation, any participant, and any last_read_message_id value,
        // the unread count should equal the number of messages in that conversation
        // where the sender is not the participant AND the message ID is greater than
        // the participant's last_read_message_id (or 0 if null).
        // **Validates: Requirements 2.1, 2.2**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Generate random conversation with random messages
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
            
            // Create participants
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $client->id,
                'last_read_message_id' => null,
            ]);
            
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $consultantUser->id,
                'last_read_message_id' => null,
            ]);
            
            // Create random number of messages from both users
            $messageCount = rand(0, 20);
            $messagesFromConsultant = 0;
            $messagesFromClient = 0;
            
            for ($j = 0; $j < $messageCount; $j++) {
                $sender = rand(0, 1) ? $client->id : $consultantUser->id;
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender,
                    'body' => "Message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
                
                if ($sender === $consultantUser->id) {
                    $messagesFromConsultant++;
                } else {
                    $messagesFromClient++;
                }
            }
            
            // Test Case 1: null last_read_message_id (should count all messages from others)
            $unreadCount = $this->conversationRepo->getUnreadCount($conversation->id, $client->id);
            $this->assertEquals($messagesFromConsultant, $unreadCount, 
                "Iteration {$i}: Unread count with null last_read_message_id should equal messages from consultant");
            
            // Test Case 2: Set last_read_message_id to middle message
            if ($messageCount > 0) {
                $messages = Message::where('conversation_id', $conversation->id)
                    ->orderBy('id')
                    ->get();
                
                $midpoint = (int) floor($messageCount / 2);
                if ($midpoint > 0 && $midpoint < $messageCount) {
                    $midMessage = $messages[$midpoint];
                    
                    DB::table('conversation_participants')
                        ->where('conversation_id', $conversation->id)
                        ->where('user_id', $client->id)
                        ->update(['last_read_message_id' => $midMessage->id]);
                    
                    // Count messages from consultant after midpoint
                    $expectedUnread = Message::where('conversation_id', $conversation->id)
                        ->where('sender_id', $consultantUser->id)
                        ->where('id', '>', $midMessage->id)
                        ->count();
                    
                    $unreadCount = $this->conversationRepo->getUnreadCount($conversation->id, $client->id);
                    $this->assertEquals($expectedUnread, $unreadCount,
                        "Iteration {$i}: Unread count should equal messages from consultant after last_read_message_id");
                }
            }
            
            // Test Case 3: All messages read (last_read_message_id = max message ID)
            if ($messageCount > 0) {
                $lastMessage = Message::where('conversation_id', $conversation->id)
                    ->orderBy('id', 'desc')
                    ->first();
                
                DB::table('conversation_participants')
                    ->where('conversation_id', $conversation->id)
                    ->where('user_id', $client->id)
                    ->update(['last_read_message_id' => $lastMessage->id]);
                
                $unreadCount = $this->conversationRepo->getUnreadCount($conversation->id, $client->id);
                $this->assertEquals(0, $unreadCount,
                    "Iteration {$i}: Unread count should be 0 when all messages are read");
            }
            
            // Test Case 4: All messages are from self (should return 0)
            // Create a new booking for this test case
            $selfOnlyBooking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_CONFIRMED,
            ]);
            
            $selfOnlyConversation = Conversation::factory()->create([
                'booking_id' => $selfOnlyBooking->id,
            ]);
            
            ConversationParticipant::factory()->create([
                'conversation_id' => $selfOnlyConversation->id,
                'user_id' => $client->id,
                'last_read_message_id' => null,
            ]);
            
            // Create messages only from client
            for ($j = 0; $j < rand(1, 5); $j++) {
                Message::factory()->create([
                    'conversation_id' => $selfOnlyConversation->id,
                    'sender_id' => $client->id,
                    'body' => "Self message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
            }
            
            $unreadCount = $this->conversationRepo->getUnreadCount($selfOnlyConversation->id, $client->id);
            $this->assertEquals(0, $unreadCount,
                "Iteration {$i}: Unread count should be 0 when all messages are from self");
            
            // Cleanup self-only booking
            $selfOnlyBooking->delete();
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Mark as Read Updates Maximum ID
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_mark_as_read_updates_maximum_id()
    {
        // Feature: chat-read-state, Property 2: Mark as Read Updates Maximum ID
        // For any conversation with messages, when a participant marks the conversation
        // as read, the participant's last_read_message_id should be updated to the
        // maximum message ID in that conversation.
        // **Validates: Requirements 3.1**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Generate random conversation with random number of messages
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
                'last_read_message_id' => null,
            ]);
            
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $consultantUser->id,
                'last_read_message_id' => null,
            ]);
            
            // Create random number of messages (1-20)
            $messageCount = rand(1, 20);
            $maxId = null;
            
            for ($j = 0; $j < $messageCount; $j++) {
                $message = Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $consultantUser->id,
                    'body' => "Message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
                $maxId = $message->id;
            }
            
            // Mark as read
            $latestMessageId = $this->participantRepo->getLatestMessageId($conversation->id);
            $this->assertEquals($maxId, $latestMessageId,
                "Iteration {$i}: getLatestMessageId should return max message ID");
            
            $result = $this->participantRepo->updateReadMarker(
                $conversation->id,
                $client->id,
                $latestMessageId
            );
            
            $this->assertTrue($result, "Iteration {$i}: updateReadMarker should return true");
            
            // Verify last_read_message_id equals max message ID
            $participant = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $client->id)
                ->first();
            
            $this->assertEquals($maxId, $participant->last_read_message_id,
                "Iteration {$i}: last_read_message_id should equal max message ID after marking as read");
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 7: Single Row Update for Mark as Read
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_single_row_update_for_mark_as_read()
    {
        // Feature: chat-read-state, Property 7: Single Row Update for Mark as Read
        // For any mark-as-read operation, the database should update exactly 1 row
        // in the conversation_participants table and 0 rows in the messages table.
        // **Validates: Requirements 5.4**
        
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
                'last_read_message_id' => null,
            ]);
            
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $consultantUser->id,
                'last_read_message_id' => null,
            ]);
            
            // Create random number of messages
            $messageCount = rand(1, 10);
            $lastMessageId = null;
            
            for ($j = 0; $j < $messageCount; $j++) {
                $message = Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $consultantUser->id,
                    'body' => "Message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
                $lastMessageId = $message->id;
            }
            
            // Count rows before update
            $participantCountBefore = DB::table('conversation_participants')->count();
            $messageCountBefore = DB::table('messages')->count();
            
            // Get checksums of messages table to verify no changes
            $messagesBeforeUpdate = DB::table('messages')
                ->select('id', 'body', 'sender_id', 'conversation_id', 'updated_at')
                ->get()
                ->toArray();
            
            // Mark as read
            $result = $this->participantRepo->updateReadMarker(
                $conversation->id,
                $client->id,
                $lastMessageId
            );
            
            $this->assertTrue($result, "Iteration {$i}: updateReadMarker should return true");
            
            // Count rows after update
            $participantCountAfter = DB::table('conversation_participants')->count();
            $messageCountAfter = DB::table('messages')->count();
            
            // Verify exactly 1 row updated in conversation_participants (count stays same)
            $this->assertEquals($participantCountBefore, $participantCountAfter,
                "Iteration {$i}: conversation_participants row count should remain the same");
            
            // Verify 0 rows updated in messages table
            $this->assertEquals($messageCountBefore, $messageCountAfter,
                "Iteration {$i}: messages row count should remain the same");
            
            // Verify messages table content unchanged
            $messagesAfterUpdate = DB::table('messages')
                ->select('id', 'body', 'sender_id', 'conversation_id', 'updated_at')
                ->get()
                ->toArray();
            
            $this->assertEquals($messagesBeforeUpdate, $messagesAfterUpdate,
                "Iteration {$i}: messages table content should be unchanged");
            
            // Verify exactly 1 participant was updated
            $updatedParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $client->id)
                ->first();
            
            $this->assertEquals($lastMessageId, $updatedParticipant->last_read_message_id,
                "Iteration {$i}: Only the target participant should be updated");
            
            // Verify other participant was NOT updated
            $otherParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $consultantUser->id)
                ->first();
            
            $this->assertNull($otherParticipant->last_read_message_id,
                "Iteration {$i}: Other participant should not be affected");
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 3: Read Marker Timestamp Update
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_read_marker_timestamp_update()
    {
        // Feature: chat-read-state, Property 3: Read Marker Timestamp Update
        // For any mark-as-read operation, the participant's last_read_at timestamp
        // should be set to the current time (within a reasonable tolerance of a few seconds).
        // **Validates: Requirements 3.2**
        
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
                'last_read_message_id' => null,
                'last_read_at' => null,
            ]);
            
            // Create at least one message
            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => "Test message",
                'type' => 'text',
                'context' => 'in_session',
            ]);
            
            // Capture time before marking as read
            $timeBefore = now();
            
            // Mark as read
            $result = $this->participantRepo->updateReadMarker(
                $conversation->id,
                $client->id,
                $message->id
            );
            
            // Capture time after marking as read
            $timeAfter = now();
            
            $this->assertTrue($result, "Iteration {$i}: updateReadMarker should return true");
            
            // Verify last_read_at is set
            $participant = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $client->id)
                ->first();
            
            $this->assertNotNull($participant->last_read_at,
                "Iteration {$i}: last_read_at should not be null after marking as read");
            
            // Verify last_read_at is within tolerance (5 seconds)
            $lastReadAt = $participant->last_read_at;
            $this->assertTrue(
                $lastReadAt->between($timeBefore->subSeconds(5), $timeAfter->addSeconds(5)),
                "Iteration {$i}: last_read_at should be within 5 seconds of current time. " .
                "Expected between {$timeBefore->toDateTimeString()} and {$timeAfter->toDateTimeString()}, " .
                "got {$lastReadAt->toDateTimeString()}"
            );
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Participant Read State Isolation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_participant_read_state_isolation()
    {
        // Feature: chat-read-state, Property 4: Participant Read State Isolation
        // For any conversation with multiple participants, when one participant marks
        // the conversation as read, the other participants' last_read_message_id and
        // last_read_at values should remain unchanged.
        // **Validates: Requirements 3.5**
        
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
            
            // Create some initial messages for participant 2 to have read
            $initialMessageCount = rand(1, 5);
            $initialMessages = [];
            for ($j = 0; $j < $initialMessageCount; $j++) {
                $initialMessages[] = Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $client->id,
                    'body' => "Initial message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
            }
            
            // Set participant 2's read state to one of the initial messages
            $originalLastReadId = $initialMessages[rand(0, count($initialMessages) - 1)]->id;
            $originalLastReadAt = now()->subMinutes(rand(1, 60));
            
            // Create participant 1 (client) with null read state
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $client->id,
                'last_read_message_id' => null,
                'last_read_at' => null,
            ]);
            
            // Create participant 2 (consultant) with existing read state
            ConversationParticipant::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $consultantUser->id,
                'last_read_message_id' => $originalLastReadId,
                'last_read_at' => $originalLastReadAt,
            ]);
            
            // Create additional messages after participant 2's read marker
            $additionalMessageCount = rand(1, 10);
            $lastMessageId = null;
            
            for ($j = 0; $j < $additionalMessageCount; $j++) {
                $message = Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $consultantUser->id,
                    'body' => "Additional message {$j}",
                    'type' => 'text',
                    'context' => 'in_session',
                ]);
                $lastMessageId = $message->id;
            }
            
            // Participant 1 (client) marks as read
            $result = $this->participantRepo->updateReadMarker(
                $conversation->id,
                $client->id,
                $lastMessageId
            );
            
            $this->assertTrue($result, "Iteration {$i}: updateReadMarker should return true");
            
            // Verify participant 1's read state was updated
            $participant1 = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $client->id)
                ->first();
            
            $this->assertEquals($lastMessageId, $participant1->last_read_message_id,
                "Iteration {$i}: Participant 1's last_read_message_id should be updated");
            $this->assertNotNull($participant1->last_read_at,
                "Iteration {$i}: Participant 1's last_read_at should be set");
            
            // Verify participant 2's read state remained unchanged
            $participant2 = ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $consultantUser->id)
                ->first();
            
            $this->assertEquals($originalLastReadId, $participant2->last_read_message_id,
                "Iteration {$i}: Participant 2's last_read_message_id should remain unchanged. " .
                "Expected {$originalLastReadId}, got {$participant2->last_read_message_id}");
            
            $this->assertEquals(
                $originalLastReadAt->toDateTimeString(),
                $participant2->last_read_at->toDateTimeString(),
                "Iteration {$i}: Participant 2's last_read_at should remain unchanged"
            );
            
            // Cleanup
            $booking->delete();
        }
    }
}
