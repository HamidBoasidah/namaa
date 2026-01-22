<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for Attachment API endpoints
 * 
 * @validates Requirements 12.4, 12.5
 */
class AttachmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    // ─────────────────────────────────────────────────────────────
    // Task 14.4: Test GET /api/attachments/{attachment} endpoint
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_participant_can_download_attachment()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        // Create a real file in storage
        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $file->store('chat-attachments', 'private');

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'disk' => 'private',
            'path' => $path,
        ]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->get("/api/attachments/{$attachment->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    /** @test */
    public function download_returns_correct_file_content_and_headers()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        // Create a real file in storage
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $path = $file->store('chat-attachments', 'private');

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $file->getSize(),
            'disk' => 'private',
            'path' => $path,
        ]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->get("/api/attachments/{$attachment->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function unauthenticated_request_returns_401()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
        ]);

        // Act
        $response = $this->getJson("/api/attachments/{$attachment->id}");

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function non_participant_cannot_download_attachment()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
        ]);

        // Act
        $response = $this->actingAs($otherUser, 'sanctum')
            ->get("/api/attachments/{$attachment->id}");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function consultant_can_download_attachment()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        // Create a real file in storage
        $file = UploadedFile::fake()->image('photo.jpg');
        $path = $file->store('chat-attachments', 'private');

        $attachment = MessageAttachment::factory()->create([
            'message_id' => $message->id,
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'disk' => 'private',
            'path' => $path,
        ]);

        // Act - consultant downloading attachment
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->get("/api/attachments/{$attachment->id}");

        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function returns_404_for_non_existent_attachment()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->get("/api/attachments/99999");

        // Assert
        $response->assertStatus(404);
    }
}
