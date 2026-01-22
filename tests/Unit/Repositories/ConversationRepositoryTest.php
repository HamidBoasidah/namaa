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
}
