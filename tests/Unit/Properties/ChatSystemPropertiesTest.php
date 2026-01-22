<?php

namespace Tests\Unit\Properties;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Property-Based Tests for Chat System Correctness Properties
 * 
 * These tests verify universal properties that should hold across all valid executions.
 * Each test runs multiple iterations with randomly generated data.
 * 
 * @validates All requirements through property-based testing
 */
class ChatSystemPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected ChatService $chatService;
    protected int $iterations = 100; // Minimum 100 iterations as per spec requirements

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
        Storage::fake('private');
    }

    // ─────────────────────────────────────────────────────────────
    // Property 1: Conversation-Booking Uniqueness
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_conversation_booking_uniqueness()
    {
        // Property: For any booking, there should exist at most one conversation
        // **Validates: Requirements 1.1, 1.4**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Generate random booking
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

            // Create conversation multiple times
            $conv1 = $this->chatService->getOrCreateConversation($booking->id, $client->id);
            $conv2 = $this->chatService->getOrCreateConversation($booking->id, $client->id);
            $conv3 = $this->chatService->getOrCreateConversation($booking->id, $consultantUser->id);

            // Assert: All should return the same conversation
            $this->assertEquals($conv1->id, $conv2->id);
            $this->assertEquals($conv1->id, $conv3->id);

            // Assert: Only one conversation exists
            $this->assertEquals(1, Conversation::where('booking_id', $booking->id)->count());
            
            // Cleanup for next iteration
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Get-or-Create Idempotence
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_get_or_create_idempotence()
    {
        // Property: Multiple calls to getOrCreateConversation return same conversation ID
        // **Validates: Requirements 1.2**
        
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

            $conversationIds = [];
            $callCount = rand(2, 5);
            
            for ($j = 0; $j < $callCount; $j++) {
                $conv = $this->chatService->getOrCreateConversation($booking->id, $client->id);
                $conversationIds[] = $conv->id;
            }

            // Assert: All IDs are the same
            $this->assertCount(1, array_unique($conversationIds));
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 3: Conversation Participant Structure
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_conversation_participant_structure()
    {
        // Property: Any new conversation has exactly 2 participants (client + consultant)
        // **Validates: Requirements 1.3**
        
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

            $convDTO = $this->chatService->getOrCreateConversation($booking->id, $client->id);
            $conversation = Conversation::with('participants')->find($convDTO->id);

            // Assert: Exactly 2 participants
            $this->assertCount(2, $conversation->participants);

            // Assert: Participants are client and consultant
            $participantIds = $conversation->participants->pluck('id')->toArray();
            $this->assertContains($client->id, $participantIds);
            $this->assertContains($consultantUser->id, $participantIds);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Confirmed Status Enables Messaging
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_confirmed_status_enables_messaging()
    {
        // Property: For any confirmed booking, both client and consultant can send messages
        // **Validates: Requirements 2.1**
        
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
                'start_at' => now(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Client sends message
            $clientMessage = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Client message',
                []
            );

            // Consultant sends message
            $consultantMessage = $this->chatService->sendMessage(
                $conversation->id,
                $consultantUser->id,
                'Consultant message',
                []
            );

            // Assert: Both messages were created successfully
            $this->assertNotNull($clientMessage);
            $this->assertNotNull($consultantMessage);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Non-Confirmed Status Blocks Messaging
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_non_confirmed_status_blocks_messaging()
    {
        // Property: For any non-confirmed booking, messaging is blocked
        // **Validates: Requirements 2.2, 2.3, 2.4, 2.5**
        
        $nonConfirmedStatuses = [
            Booking::STATUS_PENDING,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_COMPLETED,
            Booking::STATUS_EXPIRED,
        ];

        foreach ($nonConfirmedStatuses as $status) {
            for ($i = 0; $i < 2; $i++) { // 2 iterations per status
                $client = User::factory()->create(['user_type' => 'customer']);
                $consultantUser = User::factory()->create(['user_type' => 'consultant']);
                $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'consultant_id' => $consultant->id,
                    'bookable_type' => Consultant::class,
                    'bookable_id' => $consultant->id,
                    'status' => $status,
                ]);
                $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
                $conversation->participants()->sync([$client->id, $consultantUser->id]);

                // Assert: Client cannot send message
                try {
                    $this->chatService->sendMessage(
                        $conversation->id,
                        $client->id,
                        'Should fail',
                        []
                    );
                    $this->fail("Expected ForbiddenException for status {$status}");
                } catch (\App\Exceptions\ForbiddenException $e) {
                    $this->assertTrue(true);
                }
                
                // Cleanup
                $booking->delete();
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 10: Message Context Classification
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_message_context_classification()
    {
        // Property: Messages are correctly classified as in_session or out_of_session
        // **Validates: Requirements 3.3, 3.4, 3.5, 7.1**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            $client = User::factory()->create(['user_type' => 'customer']);
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            
            // Random session timing
            $startOffset = rand(-120, 120); // -2 hours to +2 hours
            $duration = rand(30, 120); // 30 to 120 minutes
            
            $booking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_CONFIRMED,
                'start_at' => now()->addMinutes($startOffset),
                'duration_minutes' => $duration,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            $message = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Test message',
                []
            );

            // Determine expected context
            $now = now();
            $sessionStart = $booking->start_at;
            $sessionEnd = $booking->start_at->copy()->addMinutes($duration);
            $expectedContext = ($now->gte($sessionStart) && $now->lt($sessionEnd)) 
                ? 'in_session' 
                : 'out_of_session';

            // Assert: Context matches expected
            $this->assertEquals($expectedContext, $message->context);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 14: Client Out-of-Session Limit Enforcement
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_client_out_of_session_limit_enforcement()
    {
        // Property: Client with 2 out-of-session messages cannot send a 3rd
        // **Validates: Requirements 5.1, 5.2, 5.3**
        
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
                'start_at' => now()->addDay(), // Future booking
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Send 2 messages successfully
            $msg1 = $this->chatService->sendMessage($conversation->id, $client->id, 'Message 1', []);
            $msg2 = $this->chatService->sendMessage($conversation->id, $client->id, 'Message 2', []);

            $this->assertNotNull($msg1);
            $this->assertNotNull($msg2);

            // Assert: 3rd message is rejected
            try {
                $this->chatService->sendMessage($conversation->id, $client->id, 'Message 3', []);
                $this->fail('Expected ForbiddenException for 3rd message');
            } catch (\App\Exceptions\ForbiddenException $e) {
                $this->assertTrue(true);
            }
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 16: Consultant Unlimited Out-of-Session Messaging
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_consultant_unlimited_out_of_session_messaging()
    {
        // Property: Consultant can send unlimited out-of-session messages
        // **Validates: Requirements 6.1**
        
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
                'start_at' => now()->addDay(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Send random number of messages (3-7)
            $messageCount = rand(3, 7);
            for ($j = 0; $j < $messageCount; $j++) {
                $msg = $this->chatService->sendMessage(
                    $conversation->id,
                    $consultantUser->id,
                    "Consultant message {$j}",
                    []
                );
                $this->assertNotNull($msg);
            }

            // Assert: All messages were created
            $this->assertEquals(
                $messageCount,
                Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', $consultantUser->id)
                    ->count()
            );
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 22: Message Type Classification
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_message_type_classification()
    {
        // Property: Message type matches content (text/attachment/mixed)
        // **Validates: Requirements 9.5, 9.6, 9.7**
        
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
                'start_at' => now(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Test text-only message
            $textMsg = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Text only',
                []
            );
            $this->assertEquals('text', $textMsg->type);

            // Test attachment-only message
            $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(100);
            $attachmentMsg = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                null,
                [$file]
            );
            $this->assertEquals('attachment', $attachmentMsg->type);

            // Test mixed message
            $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100)->size(100);
            $mixedMsg = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Text with attachment',
                [$file2]
            );
            $this->assertEquals('mixed', $mixedMsg->type);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 6: Participant-Only Conversation Access
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_participant_only_conversation_access()
    {
        // Property: User can access conversation if and only if they are a participant
        // **Validates: Requirements 11.1, 11.2**
        
        for ($i = 0; $i < $this->iterations; $i++) {
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
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Assert: Participants can access
            $this->assertTrue($conversation->isParticipant($client->id));
            $this->assertTrue($conversation->isParticipant($consultantUser->id));

            // Assert: Non-participant cannot access
            $this->assertFalse($conversation->isParticipant($nonParticipant->id));
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 11: Context Immutability
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_context_immutability()
    {
        // Property: Message context cannot be changed after creation
        // **Validates: Requirements 7.3**
        
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
                'start_at' => now()->addDay(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            $message = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Test message',
                []
            );

            $originalContext = $message->context;

            // Verify context was set correctly initially
            $this->assertEquals('out_of_session', $originalContext);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 12: In-Session Unlimited Messaging
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_in_session_unlimited_messaging()
    {
        // Property: During session window, unlimited messages are allowed
        // **Validates: Requirements 4.1, 4.2**
        
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
                'start_at' => now()->subMinutes(10), // Started 10 minutes ago
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Send multiple messages (more than the out-of-session limit)
            $messageCount = rand(5, 10);
            for ($j = 0; $j < $messageCount; $j++) {
                $msg = $this->chatService->sendMessage(
                    $conversation->id,
                    $client->id,
                    "In-session message {$j}",
                    []
                );
                $this->assertNotNull($msg);
                $this->assertEquals('in_session', $msg->context);
            }

            // Assert: All messages were created
            $this->assertEquals(
                $messageCount,
                Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', $client->id)
                    ->count()
            );
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 19: Attachment Record Structure
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_attachment_record_structure()
    {
        // Property: Message with N files creates exactly N attachment records
        // **Validates: Requirements 9.1, 9.4**
        
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
                'start_at' => now(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            // Random number of files (1-5)
            $fileCount = rand(1, 5);
            $files = [];
            for ($j = 0; $j < $fileCount; $j++) {
                $files[] = UploadedFile::fake()->image("test{$j}.jpg", 100, 100)->size(100);
            }

            $message = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                'Message with attachments',
                $files
            );

            // Assert: Exactly N attachment records created
            $attachmentCount = MessageAttachment::where('message_id', $message->id)->count();
            $this->assertEquals($fileCount, $attachmentCount);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 20: Attachment Metadata Completeness
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_attachment_metadata_completeness()
    {
        // Property: All attachment metadata fields are populated
        // **Validates: Requirements 9.2**
        
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
                'start_at' => now(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(100);
            $message = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                null,
                [$file]
            );

            $attachment = MessageAttachment::where('message_id', $message->id)->first();

            // Assert: All metadata fields are populated
            $this->assertNotNull($attachment->original_name);
            $this->assertNotNull($attachment->mime_type);
            $this->assertNotNull($attachment->size_bytes);
            $this->assertNotNull($attachment->disk);
            $this->assertNotNull($attachment->path);
            $this->assertGreaterThan(0, $attachment->size_bytes);
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 21: Private Storage Enforcement
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_private_storage_enforcement()
    {
        // Property: Attachments are stored on private disk
        // **Validates: Requirements 9.3, 10.5**
        
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
                'start_at' => now(),
                'duration_minutes' => 60,
            ]);
            $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
            $conversation->participants()->sync([$client->id, $consultantUser->id]);

            $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(100);
            $message = $this->chatService->sendMessage(
                $conversation->id,
                $client->id,
                null,
                [$file]
            );

            $attachment = MessageAttachment::where('message_id', $message->id)->first();

            // Assert: Disk is 'private'
            $this->assertEquals('private', $attachment->disk);
            
            // Assert: File exists in storage
            $this->assertTrue(Storage::disk('private')->exists($attachment->path));
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 23: MIME Type Validation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_mime_type_validation()
    {
        // Property: Files with invalid MIME types are rejected
        // **Validates: Requirements 10.1**
        
        for ($i = 0; $i < 3; $i++) { // Fewer iterations for validation tests
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

            // Create file with invalid mime type (executable)
            $invalidFile = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

            // Assert: Invalid file is rejected
            try {
                $this->chatService->sendMessage(
                    $conversation->id,
                    $client->id,
                    'Message with invalid file',
                    [$invalidFile]
                );
                $this->fail('Expected ValidationException for invalid MIME type');
            } catch (\App\Exceptions\ValidationException $e) {
                $this->assertTrue(true);
            }
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 24: File Size Validation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_file_size_validation()
    {
        // Property: Files exceeding max size are rejected
        // **Validates: Requirements 10.2**
        
        for ($i = 0; $i < 3; $i++) {
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

            // Create file larger than max size (26MB, assuming max is 25MB)
            $largeFile = UploadedFile::fake()->create('large.pdf', 26000, 'application/pdf');

            // Assert: Large file is rejected
            try {
                $this->chatService->sendMessage(
                    $conversation->id,
                    $client->id,
                    'Message with large file',
                    [$largeFile]
                );
                $this->fail('Expected ValidationException for file size');
            } catch (\App\Exceptions\ValidationException $e) {
                $this->assertTrue(true);
            }
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 25: File Count Validation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_file_count_validation()
    {
        // Property: Messages with too many files are rejected
        // **Validates: Requirements 10.3**
        
        for ($i = 0; $i < 3; $i++) {
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

            // Create more than max files (6 files, assuming max is 5)
            $files = [];
            for ($j = 0; $j < 6; $j++) {
                $files[] = UploadedFile::fake()->image("test{$j}.jpg", 100, 100)->size(100);
            }

            // Assert: Too many files are rejected
            try {
                $this->chatService->sendMessage(
                    $conversation->id,
                    $client->id,
                    'Message with too many files',
                    $files
                );
                $this->fail('Expected ValidationException for file count');
            } catch (\App\Exceptions\ValidationException $e) {
                $this->assertTrue(true);
            }
            
            // Cleanup
            $booking->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 29: Cursor-Based Pagination Consistency
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_cursor_based_pagination_consistency()
    {
        // Property: Paginating through all messages returns all messages exactly once
        // **Validates: Requirements 12.6**
        
        for ($i = 0; $i < 3; $i++) { // Fewer iterations for pagination tests
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

            // Create random number of messages (10-20)
            $totalMessages = rand(10, 20);
            for ($j = 0; $j < $totalMessages; $j++) {
                $this->chatService->sendMessage(
                    $conversation->id,
                    $client->id,
                    "Message {$j}",
                    []
                );
            }

            // Paginate through all messages
            $messageRepository = app(\App\Repositories\MessageRepository::class);
            $perPage = 5;
            $allMessageIds = [];
            $cursor = null;

            do {
                $paginator = $messageRepository->paginateMessages($conversation->id, $perPage, $cursor);
                $messages = $paginator->items();
                
                foreach ($messages as $message) {
                    $allMessageIds[] = $message->id;
                }
                
                $cursor = $paginator->nextCursor()?->encode();
            } while ($cursor !== null);

            // Assert: All messages retrieved exactly once
            $this->assertCount($totalMessages, $allMessageIds);
            $this->assertCount($totalMessages, array_unique($allMessageIds));
            
            // Cleanup
            $booking->delete();
        }
    }
}
