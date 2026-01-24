<?php

namespace Tests\Unit\Repositories;

use App\Exceptions\ValidationRuleException;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Repositories\ConversationParticipantRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationParticipantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ConversationParticipantRepository $repository;
    protected Conversation $conversation;
    protected User $user1;
    protected User $user2;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = app(ConversationParticipantRepository::class);
        
        // Create users
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        
        // Create consultant
        $consultant = Consultant::factory()->create([
            'user_id' => $this->user2->id,
        ]);
        
        // Create booking
        $this->booking = Booking::factory()->create([
            'client_id' => $this->user1->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);
        
        // Create conversation
        $this->conversation = Conversation::factory()->create([
            'booking_id' => $this->booking->id,
        ]);
        
        // Create participants
        ConversationParticipant::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user1->id,
        ]);
        
        ConversationParticipant::factory()->create([
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user2->id,
        ]);
    }

    public function test_update_read_marker_updates_last_read_message_id_and_timestamp(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user2->id,
        ]);

        $result = $this->repository->updateReadMarker(
            $this->conversation->id,
            $this->user1->id,
            $message->id
        );

        $this->assertTrue($result);
        
        $participant = ConversationParticipant::where('conversation_id', $this->conversation->id)
            ->where('user_id', $this->user1->id)
            ->first();
        
        $this->assertEquals($message->id, $participant->last_read_message_id);
        $this->assertNotNull($participant->last_read_at);
    }

    public function test_update_read_marker_throws_exception_for_invalid_message_id(): void
    {
        $this->expectException(ValidationRuleException::class);
        $this->expectExceptionMessage("Message 999999 not found in conversation {$this->conversation->id}");

        $this->repository->updateReadMarker(
            $this->conversation->id,
            $this->user1->id,
            999999
        );
    }

    public function test_update_read_marker_throws_exception_for_message_in_different_conversation(): void
    {
        // Create another conversation with proper booking
        $otherUser = User::factory()->create();
        $otherConsultant = Consultant::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $otherBooking = Booking::factory()->create([
            'client_id' => $this->user1->id,
            'consultant_id' => $otherConsultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $otherConsultant->id,
        ]);
        $otherConversation = Conversation::factory()->create([
            'booking_id' => $otherBooking->id,
        ]);
        
        $message = Message::factory()->create([
            'conversation_id' => $otherConversation->id,
            'sender_id' => $this->user2->id,
        ]);

        $this->expectException(ValidationRuleException::class);

        $this->repository->updateReadMarker(
            $this->conversation->id,
            $this->user1->id,
            $message->id
        );
    }

    public function test_update_read_marker_returns_false_for_non_existent_participant(): void
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user2->id,
        ]);
        
        $nonParticipant = User::factory()->create();

        $result = $this->repository->updateReadMarker(
            $this->conversation->id,
            $nonParticipant->id,
            $message->id
        );

        $this->assertFalse($result);
    }

    public function test_get_latest_message_id_returns_max_message_id(): void
    {
        $message1 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user1->id,
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user2->id,
        ]);
        
        $message3 = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->user1->id,
        ]);

        $latestId = $this->repository->getLatestMessageId($this->conversation->id);

        $this->assertEquals($message3->id, $latestId);
    }

    public function test_get_latest_message_id_returns_null_for_empty_conversation(): void
    {
        // Create empty conversation with proper booking
        $emptyUser = User::factory()->create();
        $emptyConsultant = Consultant::factory()->create([
            'user_id' => $emptyUser->id,
        ]);
        $emptyBooking = Booking::factory()->create([
            'client_id' => $this->user1->id,
            'consultant_id' => $emptyConsultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $emptyConsultant->id,
        ]);
        $emptyConversation = Conversation::factory()->create([
            'booking_id' => $emptyBooking->id,
        ]);

        $latestId = $this->repository->getLatestMessageId($emptyConversation->id);

        $this->assertNull($latestId);
    }

    public function test_get_latest_message_id_returns_null_for_non_existent_conversation(): void
    {
        $latestId = $this->repository->getLatestMessageId(999999);

        $this->assertNull($latestId);
    }

    public function test_get_participant_returns_participant_record(): void
    {
        $participant = $this->repository->getParticipant(
            $this->conversation->id,
            $this->user1->id
        );

        $this->assertNotNull($participant);
        $this->assertInstanceOf(ConversationParticipant::class, $participant);
        $this->assertEquals($this->conversation->id, $participant->conversation_id);
        $this->assertEquals($this->user1->id, $participant->user_id);
    }

    public function test_get_participant_returns_null_for_non_participant(): void
    {
        $nonParticipant = User::factory()->create();

        $participant = $this->repository->getParticipant(
            $this->conversation->id,
            $nonParticipant->id
        );

        $this->assertNull($participant);
    }

    public function test_get_participant_returns_null_for_non_existent_conversation(): void
    {
        $participant = $this->repository->getParticipant(
            999999,
            $this->user1->id
        );

        $this->assertNull($participant);
    }
}

