<?php

namespace Tests\Unit\Repositories;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\ConversationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConversationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ConversationRepository $repository;
    protected User $client;
    protected Consultant $consultant;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(ConversationRepository::class);
        $this->client = User::factory()->create();
        $this->consultant = Consultant::factory()->create();
        
        $this->booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    public function test_find_by_booking_returns_conversation_when_exists(): void
    {
        $conversation = Conversation::factory()->create([
            'booking_id' => $this->booking->id,
        ]);

        $found = $this->repository->findByBooking($this->booking->id);

        $this->assertNotNull($found);
        $this->assertEquals($conversation->id, $found->id);
        $this->assertEquals($this->booking->id, $found->booking_id);
    }

    public function test_find_by_booking_returns_null_when_not_exists(): void
    {
        $found = $this->repository->findByBooking($this->booking->id);

        $this->assertNull($found);
    }

    public function test_create_with_participants_creates_conversation_and_attaches_participants(): void
    {
        // Create a separate user for the consultant
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        $this->assertNotNull($conversation);
        $this->assertEquals($this->booking->id, $conversation->booking_id);
        
        // Verify participants are attached
        $this->assertCount(2, $conversation->participants);
        
        $participantIds = $conversation->participants->pluck('id')->toArray();
        $this->assertContains($this->client->id, $participantIds);
        $this->assertContains($consultantUser->id, $participantIds);
    }

    public function test_create_with_participants_uses_transaction(): void
    {
        // This test verifies that if an error occurs, the transaction rolls back
        // We'll test by trying to create with an invalid booking_id
        
        try {
            $this->repository->createWithParticipants(
                999999, // Non-existent booking
                $this->client->id,
                $this->consultant->user_id
            );
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verify no conversation was created
            $this->assertEquals(0, Conversation::count());
            
            // Verify no participants were added
            $this->assertEquals(0, DB::table('conversation_participants')->count());
        }
    }

    public function test_is_participant_returns_true_when_user_is_participant(): void
    {
        // Create a separate user for the consultant
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        $this->assertTrue($this->repository->isParticipant($conversation->id, $this->client->id));
        $this->assertTrue($this->repository->isParticipant($conversation->id, $consultantUser->id));
    }

    public function test_is_participant_returns_false_when_user_is_not_participant(): void
    {
        // Create a separate user for the consultant
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        $otherUser = User::factory()->create();

        $this->assertFalse($this->repository->isParticipant($conversation->id, $otherUser->id));
    }

    public function test_is_participant_returns_false_for_non_existent_conversation(): void
    {
        $this->assertFalse($this->repository->isParticipant(999999, $this->client->id));
    }

    public function test_get_unread_count_returns_zero_when_no_messages(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        $unreadCount = $this->repository->getUnreadCount($conversation->id, $this->client->id);

        $this->assertEquals(0, $unreadCount);
    }

    public function test_get_unread_count_returns_zero_when_last_read_message_id_is_null_and_no_messages_from_others(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create messages only from the client (self)
        $conversation->messages()->create([
            'sender_id' => $this->client->id,
            'body' => 'Message from client',
            'type' => 'text',
            'context' => 'in_session',
        ]);

        $unreadCount = $this->repository->getUnreadCount($conversation->id, $this->client->id);

        $this->assertEquals(0, $unreadCount);
    }

    public function test_get_unread_count_counts_messages_from_others_when_last_read_message_id_is_null(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create 3 messages from consultant
        for ($i = 0; $i < 3; $i++) {
            $conversation->messages()->create([
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i} from consultant",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        // Create 2 messages from client (should not be counted)
        for ($i = 0; $i < 2; $i++) {
            $conversation->messages()->create([
                'sender_id' => $this->client->id,
                'body' => "Message {$i} from client",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        $unreadCount = $this->repository->getUnreadCount($conversation->id, $this->client->id);

        $this->assertEquals(3, $unreadCount);
    }

    public function test_get_unread_count_counts_only_messages_after_last_read_message_id(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create 5 messages from consultant
        $messages = [];
        for ($i = 0; $i < 5; $i++) {
            $messages[] = $conversation->messages()->create([
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i} from consultant",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        // Mark first 3 messages as read
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $this->client->id)
            ->update(['last_read_message_id' => $messages[2]->id]);

        $unreadCount = $this->repository->getUnreadCount($conversation->id, $this->client->id);

        // Should count only messages 3 and 4 (2 messages)
        $this->assertEquals(2, $unreadCount);
    }

    public function test_get_unread_count_returns_zero_when_all_messages_are_read(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create 3 messages from consultant
        $lastMessage = null;
        for ($i = 0; $i < 3; $i++) {
            $lastMessage = $conversation->messages()->create([
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i} from consultant",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        // Mark all messages as read
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $this->client->id)
            ->update(['last_read_message_id' => $lastMessage->id]);

        $unreadCount = $this->repository->getUnreadCount($conversation->id, $this->client->id);

        $this->assertEquals(0, $unreadCount);
    }

    public function test_get_conversations_with_unread_counts_returns_empty_collection_when_no_conversations(): void
    {
        $conversations = $this->repository->getConversationsWithUnreadCounts($this->client->id);

        $this->assertCount(0, $conversations);
    }

    public function test_get_conversations_with_unread_counts_returns_conversations_with_unread_counts(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        // Create first conversation with 3 unread messages
        $conversation1 = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        for ($i = 0; $i < 3; $i++) {
            $conversation1->messages()->create([
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i}",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        // Create second conversation with 0 unread messages
        $booking2 = Booking::factory()->create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $conversation2 = $this->repository->createWithParticipants(
            $booking2->id,
            $this->client->id,
            $consultantUser->id
        );

        $lastMessage = $conversation2->messages()->create([
            'sender_id' => $consultantUser->id,
            'body' => 'Message',
            'type' => 'text',
            'context' => 'in_session',
        ]);

        // Mark conversation2 as read
        DB::table('conversation_participants')
            ->where('conversation_id', $conversation2->id)
            ->where('user_id', $this->client->id)
            ->update(['last_read_message_id' => $lastMessage->id]);

        $conversations = $this->repository->getConversationsWithUnreadCounts($this->client->id);

        $this->assertCount(2, $conversations);
        
        // Find conversation1 in results
        $conv1Result = $conversations->firstWhere('id', $conversation1->id);
        $this->assertNotNull($conv1Result);
        $this->assertEquals(3, $conv1Result->unread_count);
        
        // Find conversation2 in results
        $conv2Result = $conversations->firstWhere('id', $conversation2->id);
        $this->assertNotNull($conv2Result);
        $this->assertEquals(0, $conv2Result->unread_count);
    }

    public function test_get_conversations_with_unread_counts_excludes_soft_deleted_conversations(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        $conversation->messages()->create([
            'sender_id' => $consultantUser->id,
            'body' => 'Message',
            'type' => 'text',
            'context' => 'in_session',
        ]);

        // Soft delete the conversation
        $conversation->delete();

        $conversations = $this->repository->getConversationsWithUnreadCounts($this->client->id);

        $this->assertCount(0, $conversations);
    }

    public function test_get_conversations_with_unread_counts_orders_by_updated_at_desc(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        // Create first conversation
        $conversation1 = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create second conversation (newer)
        $booking2 = Booking::factory()->create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $conversation2 = $this->repository->createWithParticipants(
            $booking2->id,
            $this->client->id,
            $consultantUser->id
        );

        // Update conversation1 to be more recent
        $conversation1->touch();

        $conversations = $this->repository->getConversationsWithUnreadCounts($this->client->id);

        $this->assertCount(2, $conversations);
        $this->assertEquals($conversation1->id, $conversations->first()->id);
        $this->assertEquals($conversation2->id, $conversations->last()->id);
    }

    public function test_get_conversations_with_unread_counts_does_not_count_own_messages(): void
    {
        $consultantUser = User::factory()->create();
        $this->consultant->update(['user_id' => $consultantUser->id]);
        
        $conversation = $this->repository->createWithParticipants(
            $this->booking->id,
            $this->client->id,
            $consultantUser->id
        );

        // Create 5 messages from client (should not be counted for client)
        for ($i = 0; $i < 5; $i++) {
            $conversation->messages()->create([
                'sender_id' => $this->client->id,
                'body' => "Message {$i} from client",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        // Create 2 messages from consultant (should be counted for client)
        for ($i = 0; $i < 2; $i++) {
            $conversation->messages()->create([
                'sender_id' => $consultantUser->id,
                'body' => "Message {$i} from consultant",
                'type' => 'text',
                'context' => 'in_session',
            ]);
        }

        $conversations = $this->repository->getConversationsWithUnreadCounts($this->client->id);

        $this->assertCount(1, $conversations);
        $this->assertEquals(2, $conversations->first()->unread_count);
    }
}
