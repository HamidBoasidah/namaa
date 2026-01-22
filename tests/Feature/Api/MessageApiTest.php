<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for Message API endpoints
 * 
 * @validates Requirements 12.2, 12.3, 12.5, 12.6
 */
class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    // ─────────────────────────────────────────────────────────────
    // Task 14.2: Test GET /api/conversations/{conversation}/messages endpoint
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_participant_can_list_messages()
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

        // Create some messages
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'conversation_id',
                        'sender_id',
                        'body',
                        'type',
                        'context',
                        'created_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function cursor_based_pagination_works_correctly()
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

        // Create 10 messages
        Message::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
        ]);

        // Act - get first page
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages?per_page=5");

        // Assert
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function unauthenticated_request_to_list_messages_returns_401()
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

        // Act
        $response = $this->getJson("/api/conversations/{$conversation->id}/messages");

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function non_participant_cannot_list_messages()
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

        // Act
        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function returns_correct_json_structure_with_attachments()
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

        // Create message with attachment
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'type' => 'mixed',
        ]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'conversation_id',
                        'sender_id',
                        'body',
                        'type',
                        'context',
                        'attachments',
                        'created_at',
                    ],
                ],
            ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Task 14.3: Test POST /api/conversations/{conversation}/messages endpoint
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_participant_can_send_text_message()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'body' => 'Hello, this is a test message',
            ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'conversation_id',
                    'sender_id',
                    'body',
                    'type',
                    'context',
                    'created_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'body' => 'Hello, this is a test message',
                    'type' => 'text',
                ],
            ]);
    }

    /** @test */
    public function authenticated_participant_can_send_message_with_attachments()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->image('photo.jpg');

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'body' => 'Here is a photo',
                'files' => [$file],
            ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'mixed',
                ],
            ]);
    }

    /** @test */
    public function authenticated_participant_can_send_attachment_only_message()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->image('photo.jpg');

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'files' => [$file],
            ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'attachment',
                ],
            ]);
    }

    /** @test */
    public function empty_message_returns_422()
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

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", []);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function invalid_file_type_returns_422()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'files' => [$file],
            ]);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function file_too_large_returns_422()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $file = UploadedFile::fake()->create('large.pdf', 30000); // 30MB

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'files' => [$file],
            ]);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function too_many_files_returns_422()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        $files = [];
        for ($i = 0; $i < 6; $i++) {
            $files[] = UploadedFile::fake()->image("image{$i}.jpg");
        }

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'files' => $files,
            ]);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function client_exceeding_limit_returns_403()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
            'start_at' => now()->addDay(), // Future booking
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Create 2 out-of-session messages
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'context' => 'out_of_session',
        ]);

        // Act - try to send third message
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'body' => 'Third message',
            ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function non_confirmed_booking_returns_403()
    {
        // Arrange
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'status' => Booking::STATUS_PENDING,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $response = $this->actingAs($client, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'body' => 'Test message',
            ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_request_to_send_message_returns_401()
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

        // Act
        $response = $this->postJson("/api/conversations/{$conversation->id}/messages", [
            'body' => 'Test message',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function non_participant_cannot_send_message()
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
            'start_at' => now(),
            'duration_minutes' => 60,
        ]);
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act
        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'body' => 'Test message',
            ]);

        // Assert
        $response->assertStatus(403);
    }
}
