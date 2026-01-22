<?php

namespace Tests\Unit\Services;

use App\DTOs\ConversationDTO;
use App\DTOs\MessageDTO;
use App\Exceptions\ForbiddenException;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
    }

    /** @test */
    public function it_creates_conversation_for_new_booking()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act
        $conversationDTO = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Assert
        $this->assertInstanceOf(ConversationDTO::class, $conversationDTO);
        $this->assertEquals($booking->id, $conversationDTO->booking_id);
        $this->assertCount(2, $conversationDTO->participants);
        
        // Verify conversation exists in database
        $this->assertDatabaseHas('conversations', [
            'booking_id' => $booking->id,
        ]);
    }

    /** @test */
    public function it_returns_existing_conversation_for_booking()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Create conversation first time
        $firstConversation = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Act - try to create again
        $secondConversation = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Assert - should return same conversation
        $this->assertEquals($firstConversation->id, $secondConversation->id);
        
        // Verify only one conversation exists
        $this->assertEquals(1, Conversation::where('booking_id', $booking->id)->count());
    }

    /** @test */
    public function it_throws_exception_if_user_not_participant()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act & Assert
        $this->expectException(ForbiddenException::class);
        $this->chatService->getOrCreateConversation($booking->id, $otherUser->id);
    }

    /** @test */
    public function it_determines_in_session_correctly()
    {
        // Arrange - booking starting now, 60 minutes duration
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now(),
            'duration_minutes' => 60,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act
        $isInSession = $this->chatService->isInSession($booking);

        // Assert
        $this->assertTrue($isInSession);
    }

    /** @test */
    public function it_determines_out_of_session_before_start()
    {
        // Arrange - booking starting in 1 hour
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act
        $isInSession = $this->chatService->isInSession($booking);

        // Assert
        $this->assertFalse($isInSession);
    }

    /** @test */
    public function it_determines_out_of_session_after_end()
    {
        // Arrange - booking ended 1 hour ago
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'start_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act
        $isInSession = $this->chatService->isInSession($booking);

        // Assert
        $this->assertFalse($isInSession);
    }

    /** @test */
    public function it_sends_message_successfully_in_session()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Hello, this is a test message',
            []
        );

        // Assert
        $this->assertInstanceOf(MessageDTO::class, $messageDTO);
        $this->assertEquals('Hello, this is a test message', $messageDTO->body);
        $this->assertEquals('text', $messageDTO->type);
        $this->assertEquals('in_session', $messageDTO->context);
    }

    /** @test */
    public function it_blocks_message_for_non_confirmed_booking()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_PENDING,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act & Assert
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Messaging is only allowed for confirmed bookings');
        
        $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'This should fail',
            []
        );
    }

    /** @test */
    public function it_enforces_client_out_of_session_message_limit()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDay(), // Future booking (out of session)
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create 2 out-of-session messages from client
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'context' => 'out_of_session',
        ]);

        // Act & Assert - third message should be blocked
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('You have reached the maximum of 2 messages outside the session window');
        
        $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'This is the third message',
            []
        );
    }

    /** @test */
    public function it_allows_consultant_unlimited_out_of_session_messages()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => \App\Models\Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDay(), // Future booking (out of session)
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create 5 out-of-session messages from consultant user
        Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $consultantUser->id,
            'context' => 'out_of_session',
        ]);

        // Act - consultant should be able to send more
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            $consultantUser->id,
            'Consultant can send unlimited messages',
            []
        );

        // Assert
        $this->assertInstanceOf(MessageDTO::class, $messageDTO);
        $this->assertEquals('out_of_session', $messageDTO->context);
    }

    /** @test */
    public function it_counts_client_out_of_session_messages_correctly()
    {
        // Arrange
        $client = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);

        // Create 2 out-of-session messages from client
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'context' => 'out_of_session',
        ]);

        // Create 1 in-session message from client (should not count)
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'context' => 'in_session',
        ]);

        // Act
        $count = $this->chatService->countClientOutOfSessionMessages($conversation->id, $client->id);

        // Assert
        $this->assertEquals(2, $count);
    }
}
