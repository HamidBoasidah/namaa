<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Repositories\AttachmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AttachmentRepository $repository;
    protected User $client;
    protected Consultant $consultant;
    protected Conversation $conversation;
    protected Message $message;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(AttachmentRepository::class);
        $this->client = User::factory()->create();
        
        // Create consultant with a separate user
        $consultantUser = User::factory()->create();
        $this->consultant = Consultant::factory()->create([
            'user_id' => $consultantUser->id,
        ]);
        
        $booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
        
        $this->conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);
        
        $this->conversation->participants()->attach([$this->client->id, $consultantUser->id]);
        
        $this->message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
        ]);
    }

    public function test_create_creates_attachment_record(): void
    {
        $data = [
            'message_id' => $this->message->id,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1048576,
            'disk' => 'private',
            'path' => 'chat-attachments/1/1/uuid.pdf',
        ];

        $attachment = $this->repository->create($data);

        $this->assertNotNull($attachment);
        $this->assertEquals($this->message->id, $attachment->message_id);
        $this->assertEquals('document.pdf', $attachment->original_name);
        $this->assertEquals('application/pdf', $attachment->mime_type);
        $this->assertEquals(1048576, $attachment->size_bytes);
        $this->assertEquals('private', $attachment->disk);
        $this->assertEquals('chat-attachments/1/1/uuid.pdf', $attachment->path);
        
        // Verify it exists in database
        $this->assertDatabaseHas('message_attachments', [
            'id' => $attachment->id,
            'message_id' => $this->message->id,
            'original_name' => 'document.pdf',
        ]);
    }

    public function test_find_with_relations_loads_message_and_conversation(): void
    {
        $attachment = MessageAttachment::factory()->create([
            'message_id' => $this->message->id,
        ]);

        $result = $this->repository->findWithRelations($attachment->id);

        $this->assertNotNull($result);
        $this->assertEquals($attachment->id, $result->id);
        
        // Verify relationships are loaded
        $this->assertTrue($result->relationLoaded('message'));
        $this->assertTrue($result->message->relationLoaded('conversation'));
        $this->assertTrue($result->message->conversation->relationLoaded('participants'));
        
        // Verify relationship data
        $this->assertEquals($this->message->id, $result->message->id);
        $this->assertEquals($this->conversation->id, $result->message->conversation->id);
    }

    public function test_find_with_relations_returns_null_for_nonexistent_attachment(): void
    {
        $result = $this->repository->findWithRelations(99999);

        $this->assertNull($result);
    }

    public function test_find_with_relations_includes_participants_for_authorization(): void
    {
        $attachment = MessageAttachment::factory()->create([
            'message_id' => $this->message->id,
        ]);

        $result = $this->repository->findWithRelations($attachment->id);

        $this->assertNotNull($result);
        
        // Verify participants are loaded for authorization checks
        $participants = $result->message->conversation->participants;
        $this->assertCount(2, $participants);
        
        $participantIds = $participants->pluck('id')->toArray();
        $this->assertContains($this->client->id, $participantIds);
        $this->assertContains($this->consultant->user->id, $participantIds);
    }
}
