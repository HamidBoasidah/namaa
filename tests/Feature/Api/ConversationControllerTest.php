<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for ConversationController
 * 
 * Tests the GET /api/bookings/{booking}/conversation endpoint
 */
class ConversationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $consultantUser;
    protected Consultant $consultant;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create client
        $this->client = User::factory()->create();

        // Create consultant user and consultant
        $this->consultantUser = User::factory()->create();
        $this->consultant = Consultant::factory()->create([
            'user_id' => $this->consultantUser->id,
        ]);

        // Create a confirmed booking
        $this->booking = Booking::factory()->create([
            'client_id' => $this->client->id,
            'consultant_id' => $this->consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Test authenticated client can get or create conversation for their booking
     */
    public function test_authenticated_client_can_get_or_create_conversation(): void
    {
        $response = $this->actingAs($this->client)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'booking_id',
                    'participants',
                    'created_at',
                ],
            ]);

        // Verify conversation was created
        $this->assertDatabaseHas('conversations', [
            'booking_id' => $this->booking->id,
        ]);

        // Verify participants were added
        $conversation = Conversation::where('booking_id', $this->booking->id)->first();
        $this->assertNotNull($conversation);
        $this->assertTrue($conversation->isParticipant($this->client->id));
        $this->assertTrue($conversation->isParticipant($this->consultantUser->id));
    }

    /**
     * Test authenticated consultant can get or create conversation for their booking
     */
    public function test_authenticated_consultant_can_get_or_create_conversation(): void
    {
        $response = $this->actingAs($this->consultantUser)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_code' => 200,
            ]);

        // Verify conversation was created
        $this->assertDatabaseHas('conversations', [
            'booking_id' => $this->booking->id,
        ]);
    }

    /**
     * Test unauthenticated request returns 401
     */
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson("/api/bookings/{$this->booking->id}/conversation");

        $response->assertStatus(401);
    }

    /**
     * Test non-participant returns 403
     */
    public function test_non_participant_returns_403(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $response->assertStatus(403);
    }

    /**
     * Test returns existing conversation if one already exists
     */
    public function test_returns_existing_conversation_if_already_exists(): void
    {
        // Create conversation first
        $firstResponse = $this->actingAs($this->client)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $firstResponse->assertStatus(200);
        $firstConversationId = $firstResponse->json('data.id');

        // Request again
        $secondResponse = $this->actingAs($this->client)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $secondResponse->assertStatus(200);
        $secondConversationId = $secondResponse->json('data.id');

        // Should return the same conversation
        $this->assertEquals($firstConversationId, $secondConversationId);

        // Verify only one conversation exists for this booking
        $this->assertEquals(1, Conversation::where('booking_id', $this->booking->id)->count());
    }

    /**
     * Test non-existent booking returns 404
     */
    public function test_non_existent_booking_returns_404(): void
    {
        $nonExistentId = 99999;

        $response = $this->actingAs($this->client)
            ->getJson("/api/bookings/{$nonExistentId}/conversation");

        $response->assertStatus(404);
    }

    /**
     * Test response includes correct participant information
     */
    public function test_response_includes_correct_participant_information(): void
    {
        $response = $this->actingAs($this->client)
            ->getJson("/api/bookings/{$this->booking->id}/conversation");

        $response->assertStatus(200);

        $participants = $response->json('data.participants');
        $this->assertCount(2, $participants);

        // Verify both client and consultant are in participants
        $participantIds = array_column($participants, 'id');
        $this->assertContains($this->client->id, $participantIds);
        $this->assertContains($this->consultantUser->id, $participantIds);
    }
}
