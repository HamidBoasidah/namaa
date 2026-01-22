<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Conversation API endpoints
 * 
 * @validates Requirements 12.1, 12.5
 */
class ConversationApiTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Task 14.1: Test GET /api/bookings/{booking}/conversation endpoint
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function authenticated_user_can_get_or_create_conversation()
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
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert
        $response->assertStatus(200)
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
            'booking_id' => $booking->id,
        ]);
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
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Act
        $response = $this->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function non_participant_returns_403()
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

        // Act
        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function returns_correct_json_structure()
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
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'booking_id',
                    'participants' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'created_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'booking_id' => $booking->id,
                ],
            ]);

        // Verify participants array has 2 users
        $this->assertCount(2, $response->json('data.participants'));
    }

    /** @test */
    public function consultant_can_access_conversation()
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

        // Act - consultant user accessing the conversation
        $response = $this->actingAs($consultantUser, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'booking_id' => $booking->id,
                ],
            ]);
    }

    /** @test */
    public function returns_existing_conversation_if_already_created()
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

        // Create conversation first
        $conversation = Conversation::factory()->create(['booking_id' => $booking->id]);
        $conversation->participants()->sync([$client->id, $consultantUser->id]);

        // Act - request conversation again
        $response = $this->actingAs($client, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/conversation");

        // Assert - should return the same conversation
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $conversation->id,
                    'booking_id' => $booking->id,
                ],
            ]);

        // Verify only one conversation exists
        $this->assertEquals(1, Conversation::where('booking_id', $booking->id)->count());
    }
}
