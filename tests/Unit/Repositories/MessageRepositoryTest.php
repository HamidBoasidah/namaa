<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Repositories\MessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MessageRepository $repository;
    protected User $client;
    protected Consultant $consultant;
    protected Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(MessageRepository::class);
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
    }

    public function test_create_creates_message_with_relationships(): void
    {
        $data = [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'body' => 'Test message',
            'type' => 'text',
            'context' => 'out_of_session',
        ];

        $message = $this->repository->create($data);

        $this->assertNotNull($message);
        $this->assertEquals($this->conversation->id, $message->conversation_id);
        $this->assertEquals($this->client->id, $message->sender_id);
        $this->assertEquals('Test message', $message->body);
        $this->assertEquals('text', $message->type);
        $this->assertEquals('out_of_session', $message->context);
        
        // Verify relationships are loaded
        $this->assertTrue($message->relationLoaded('sender'));
        $this->assertTrue($message->relationLoaded('attachments'));
    }

    public function test_count_out_of_session_with_lock_counts_correctly(): void
    {
        // Get consultant user
        $consultantUser = $this->consultant->user;
        
        // Create 2 out-of-session messages from client
        Message::factory()->count(2)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'context' => 'out_of_session',
        ]);
        
        // Create 1 in-session message from client (should not be counted)
        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'context' => 'in_session',
        ]);
        
        // Create 1 out-of-session message from consultant (should not be counted)
        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $consultantUser->id,
            'context' => 'out_of_session',
        ]);

        $count = DB::transaction(function () {
            return $this->repository->countOutOfSessionWithLock(
                $this->conversation->id,
                $this->client->id
            );
        });

        $this->assertEquals(2, $count);
    }

    public function test_count_out_of_session_with_lock_returns_zero_when_no_messages(): void
    {
        $count = DB::transaction(function () {
            return $this->repository->countOutOfSessionWithLock(
                $this->conversation->id,
                $this->client->id
            );
        });

        $this->assertEquals(0, $count);
    }

    public function test_paginate_messages_returns_messages_in_descending_order(): void
    {
        // Create messages with different timestamps
        $message1 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'created_at' => now()->subMinutes(10),
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'created_at' => now()->subMinutes(5),
        ]);
        
        $message3 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
            'created_at' => now(),
        ]);

        $paginator = $this->repository->paginateMessages($this->conversation->id, 10);

        $this->assertCount(3, $paginator->items());
        
        // Verify order (newest first)
        $this->assertEquals($message3->id, $paginator->items()[0]->id);
        $this->assertEquals($message2->id, $paginator->items()[1]->id);
        $this->assertEquals($message1->id, $paginator->items()[2]->id);
    }

    public function test_paginate_messages_eager_loads_relationships(): void
    {
        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
        ]);

        $paginator = $this->repository->paginateMessages($this->conversation->id, 10);

        $message = $paginator->items()[0];
        
        // Verify relationships are loaded
        $this->assertTrue($message->relationLoaded('sender'));
        $this->assertTrue($message->relationLoaded('attachments'));
    }

    public function test_paginate_messages_filters_by_conversation(): void
    {
        // Create message in this conversation
        Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
        ]);
        
        // Create message in another conversation
        $otherConversation = Conversation::factory()->create();
        Message::factory()->create([
            'conversation_id' => $otherConversation->id,
            'sender_id' => $this->client->id,
        ]);

        $paginator = $this->repository->paginateMessages($this->conversation->id, 10);

        $this->assertCount(1, $paginator->items());
        $this->assertEquals($this->conversation->id, $paginator->items()[0]->conversation_id);
    }

    public function test_paginate_messages_respects_per_page_limit(): void
    {
        // Create 15 messages
        Message::factory()->count(15)->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->client->id,
        ]);

        $paginator = $this->repository->paginateMessages($this->conversation->id, 10);

        $this->assertCount(10, $paginator->items());
        $this->assertTrue($paginator->hasMorePages());
    }
}

