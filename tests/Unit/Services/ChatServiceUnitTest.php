<?php

namespace Tests\Unit\Services;

use App\Exceptions\ForbiddenException;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for ChatService core functionality
 * 
 * @validates Requirements 1.2, 1.3, 2.1-2.5, 3.3-3.5, 5.1-5.3, 6.1, 9.5-9.7, 10.1-10.4, 11.1-11.3
 */
class ChatServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    protected ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
        Storage::fake('private');
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.1: Test conversation creation with participants
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_creates_conversation_with_exactly_two_participants()
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

        // Act
        $conversationDTO = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Assert
        $conversation = Conversation::find($conversationDTO->id);
        $this->assertNotNull($conversation);
        $this->assertCount(2, $conversation->participants);
        
        // Verify both client and consultant are participants
        $participantIds = $conversation->participants->pluck('id')->toArray();
        $this->assertContains($client->id, $participantIds);
        $this->assertContains($consultantUser->id, $participantIds);
    }

    /** @test */
    public function it_returns_existing_conversation_instead_of_creating_duplicate()
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

        // Act - create conversation twice
        $firstConversation = $this->chatService->getOrCreateConversation($booking->id, $client->id);
        $secondConversation = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Assert - should return same conversation
        $this->assertEquals($firstConversation->id, $secondConversation->id);
        
        // Verify only one conversation exists for this booking
        $this->assertEquals(1, Conversation::where('booking_id', $booking->id)->count());
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.2: Test message context determination
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_sets_in_session_context_during_session_window()
    {
        // Arrange - booking is currently in session
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->subMinutes(10), // Started 10 minutes ago
            'duration_minutes' => 60, // Still 50 minutes left
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Message during session',
            []
        );

        // Assert
        $this->assertEquals('in_session', $messageDTO->context);
        $this->assertDatabaseHas('messages', [
            'id' => $messageDTO->id,
            'context' => 'in_session',
        ]);
    }

    /** @test */
    public function it_sets_out_of_session_context_before_session()
    {
        // Arrange - booking starts in the future
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addHours(2), // Starts in 2 hours
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Message before session',
            []
        );

        // Assert
        $this->assertEquals('out_of_session', $messageDTO->context);
        $this->assertDatabaseHas('messages', [
            'id' => $messageDTO->id,
            'context' => 'out_of_session',
        ]);
    }

    /** @test */
    public function it_sets_out_of_session_context_after_session()
    {
        // Arrange - booking ended in the past
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->subHours(2), // Started 2 hours ago
            'duration_minutes' => 60, // Ended 1 hour ago
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $messageDTO = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Message after session',
            []
        );

        // Assert
        $this->assertEquals('out_of_session', $messageDTO->context);
        $this->assertDatabaseHas('messages', [
            'id' => $messageDTO->id,
            'context' => 'out_of_session',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.3: Test client message limit enforcement
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_allows_client_to_send_two_out_of_session_messages()
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
            'start_at' => now()->addDay(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act - send first message
        $message1 = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'First message',
            []
        );

        // Act - send second message
        $message2 = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Second message',
            []
        );

        // Assert
        $this->assertNotNull($message1);
        $this->assertNotNull($message2);
        $this->assertEquals(2, Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $client->id)
            ->where('context', 'out_of_session')
            ->count());
    }

    /** @test */
    public function it_blocks_client_from_sending_third_out_of_session_message()
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
            'start_at' => now()->addDay(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create 2 existing out-of-session messages
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
            'Third message should fail',
            []
        );
    }

    /** @test */
    public function it_enforces_limit_per_conversation()
    {
        // Arrange - create two different bookings/conversations
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        
        $booking1 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDay(),
            'duration_minutes' => 60,
        ]);
        $conversation1 = Conversation::factory()->create(['booking_id' => $booking1->id]);
        $conversation1->participants()->sync([$client->id, $consultantUser->id]);

        $booking2 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDays(2),
            'duration_minutes' => 60,
        ]);
        $conversation2 = Conversation::factory()->create(['booking_id' => $booking2->id]);
        $conversation2->participants()->sync([$client->id, $consultantUser->id]);

        // Create 2 messages in first conversation
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation1->id,
            'sender_id' => $client->id,
            'context' => 'out_of_session',
        ]);

        // Act - client should still be able to send 2 messages in second conversation
        $message1 = $this->chatService->sendMessage(
            $conversation2->id,
            $client->id,
            'First message in conversation 2',
            []
        );

        $message2 = $this->chatService->sendMessage(
            $conversation2->id,
            $client->id,
            'Second message in conversation 2',
            []
        );

        // Assert
        $this->assertNotNull($message1);
        $this->assertNotNull($message2);
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.4: Test consultant unlimited messaging
    // ─────────────────────────────────────────────────────────────

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
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDay(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act - send 5 messages from consultant
        for ($i = 1; $i <= 5; $i++) {
            $message = $this->chatService->sendMessage(
                $conversation->id,
                $consultantUser->id,
                "Consultant message {$i}",
                []
            );
            $this->assertNotNull($message);
        }

        // Assert - all 5 messages should be created
        $this->assertEquals(5, Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $consultantUser->id)
            ->where('context', 'out_of_session')
            ->count());
    }

    /** @test */
    public function it_allows_consultant_unlimited_in_session_messages()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act - send 10 messages from consultant during session
        for ($i = 1; $i <= 10; $i++) {
            $message = $this->chatService->sendMessage(
                $conversation->id,
                $consultantUser->id,
                "In-session message {$i}",
                []
            );
            $this->assertNotNull($message);
        }

        // Assert - all 10 messages should be created
        $this->assertEquals(10, Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $consultantUser->id)
            ->where('context', 'in_session')
            ->count());
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.5: Test booking status authorization
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_allows_messaging_for_confirmed_bookings()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $message = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Message for confirmed booking',
            []
        );

        // Assert
        $this->assertNotNull($message);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'body' => 'Message for confirmed booking',
        ]);
    }

    /** @test */
    public function it_blocks_messaging_for_pending_bookings()
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
    public function it_blocks_messaging_for_cancelled_bookings()
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
            'status' => Booking::STATUS_CANCELLED,
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
    public function it_blocks_messaging_for_completed_bookings()
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
            'status' => Booking::STATUS_COMPLETED,
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
    public function it_blocks_messaging_for_expired_bookings()
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
            'status' => Booking::STATUS_EXPIRED,
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

    // ─────────────────────────────────────────────────────────────
    // Task 13.6: Test attachment validation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_validates_file_mime_type()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create invalid file type
        $invalidFile = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        // Act & Assert
        $this->expectException(ValidationException::class);

        $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            null,
            [$invalidFile]
        );
    }

    /** @test */
    public function it_validates_file_size()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create file larger than max size (assuming 25MB limit)
        $largeFile = UploadedFile::fake()->create('large.pdf', 30000); // 30MB

        // Act & Assert
        $this->expectException(ValidationException::class);

        $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            null,
            [$largeFile]
        );
    }

    /** @test */
    public function it_validates_file_count()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create more than max files (assuming 5 file limit)
        $files = [];
        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("image{$i}.jpg");
        }

        // Act & Assert
        $this->expectException(ValidationException::class);

        $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            null,
            $files
        );
    }

    /** @test */
    public function it_rejects_entire_message_when_validation_fails()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create mix of valid and invalid files
        $validFile = UploadedFile::fake()->image('valid.jpg');
        $invalidFile = UploadedFile::fake()->create('invalid.exe', 100, 'application/x-msdownload');

        $messageCountBefore = Message::where('conversation_id', $conversation->id)->count();

        // Act & Assert
        try {
            $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Message with mixed files',
                [$validFile, $invalidFile]
            );
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            // Assert - no message should be created
            $messageCountAfter = Message::where('conversation_id', $conversation->id)->count();
            $this->assertEquals($messageCountBefore, $messageCountAfter);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.7: Test message type classification
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_sets_type_text_for_text_only_message()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $message = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Text only message',
            []
        );

        // Assert
        $this->assertEquals('text', $message->type);
    }

    /** @test */
    public function it_sets_type_attachment_for_attachment_only_message()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->image('photo.jpg');

        // Act
        $message = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            null,
            [$file]
        );

        // Assert
        $this->assertEquals('attachment', $message->type);
    }

    /** @test */
    public function it_sets_type_mixed_for_text_and_attachment_message()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->image('photo.jpg');

        // Act
        $message = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Here is a photo',
            [$file]
        );

        // Assert
        $this->assertEquals('mixed', $message->type);
    }

    // ─────────────────────────────────────────────────────────────
    // Task 13.8: Test participant authorization
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function it_allows_participant_to_access_conversation()
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

        // Act
        $conversationDTO = $this->chatService->getOrCreateConversation($booking->id, $client->id);

        // Assert
        $this->assertNotNull($conversationDTO);
        $this->assertEquals($booking->id, $conversationDTO->booking_id);
    }

    /** @test */
    public function it_blocks_non_participant_from_accessing_conversation()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act & Assert
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('You are not a participant in this booking');

        $this->chatService->getOrCreateConversation($booking->id, $otherUser->id);
    }

    /** @test */
    public function it_allows_participant_to_send_message()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $message = $this->chatService->sendMessage(
            $conversation->id,
            $client->id,
            'Participant message',
            []
        );

        // Assert
        $this->assertNotNull($message);
    }

    /** @test */
    public function it_blocks_non_participant_from_sending_message()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act & Assert
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('You are not a participant in this conversation');

        $this->chatService->sendMessage(
            $conversation->id,
            $otherUser->id,
            'Non-participant message',
            []
        );
    }
}
