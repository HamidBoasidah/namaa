<?php

namespace Tests\Unit\Services;

use App\DTOs\MarkReadDTO;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use App\Services\ReadStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Tests for ReadStateService
 * 
 * Tests the service layer for read state functionality including:
 * - markAsRead with explicit message ID
 * - markAsRead with null message ID (uses latest)
 * - markAsRead on empty conversation
 * - getMessagesAndMarkRead integration
 * 
 * **Validates: Requirements 3.1, 3.2, 4.2**
 */
class ReadStateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReadStateService $readStateService;
    protected ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->readStateService = app(ReadStateService::class);
        $this->chatService = app(ChatService::class);
    }

    /** @test */
    public function it_marks_conversation_as_read_with_explicit_message_id()
    {
        // Arrange
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
        
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => null,
        ]);
        
        // Create messages
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
        
        // Act
        $dto = new MarkReadDTO($conversation->id, $client->id, $message1->id);
        $result = $this->readStateService->markAsRead($dto);
        
        // Assert
        $this->assertTrue($result);
        
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertEquals($message1->id, $participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }

    /** @test */
    public function it_marks_conversation_as_read_with_null_message_id_uses_latest()
    {
        // Arrange
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
        
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => null,
        ]);
        
        // Create messages
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
        
        // Act - Pass null message ID
        $dto = new MarkReadDTO($conversation->id, $client->id, null);
        $result = $this->readStateService->markAsRead($dto);
        
        // Assert
        $this->assertTrue($result);
        
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        // Should use the latest message ID (message2)
        $this->assertEquals($message2->id, $participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }

    /** @test */
    public function it_handles_mark_as_read_on_empty_conversation()
    {
        // Arrange
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
        
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => null,
        ]);
        
        // No messages created
        
        // Act
        $dto = new MarkReadDTO($conversation->id, $client->id, null);
        $result = $this->readStateService->markAsRead($dto);
        
        // Assert
        $this->assertTrue($result);
        
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        // Should remain null since there are no messages
        $this->assertNull($participant->last_read_message_id);
    }

    /** @test */
    public function it_gets_unread_count_for_conversation()
    {
        // Arrange
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
        
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $client->id,
            'last_read_message_id' => null,
        ]);
        
        // Create 3 messages from consultant
        for ($i = 0; $i < 3; $i++) {
            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i}",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }
        
        // Act
        $unreadCount = $this->readStateService->getUnreadCount($conversation->id, $client->id);
        
        // Assert
        $this->assertEquals(3, $unreadCount);
    }

    /** @test */
    public function it_integrates_get_messages_and_mark_read()
    {
        // Arrange
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
        
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        
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
        
        // Create messages
        for ($i = 0; $i < 5; $i++) {
            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i}",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }
        
        // Act
        $result = $this->chatService->getMessagesAndMarkRead(
            $conversation->id,
            $client->id,
            50,
            null
        );
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('messages', $result);
        $this->assertArrayHasKey('unread_count', $result);
        
        // Messages should be returned
        $this->assertCount(5, $result['messages']);
        
        // Unread count should be 0 after marking as read
        $this->assertEquals(0, $result['unread_count']);
        
        // Verify participant's read marker was updated
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $client->id)
            ->first();
        
        $this->assertNotNull($participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }
}
