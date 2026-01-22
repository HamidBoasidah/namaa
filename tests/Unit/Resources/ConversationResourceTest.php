<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Consultant;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_resource_transforms_model_correctly(): void
    {
        // Create users and consultant
        $client = User::factory()->create([
            'first_name' => 'Client',
            'last_name' => 'User',
            'user_type' => 'customer'
        ]);
        $consultant = Consultant::factory()->create();
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);

        $conversation->participants()->attach([$client->id, $consultant->user_id]);

        // Transform using resource
        $resource = new ConversationResource($conversation->load('participants'));
        $array = $resource->toArray(request());

        // Assert structure
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('booking_id', $array);
        $this->assertArrayHasKey('participants', $array);
        $this->assertArrayHasKey('created_at', $array);

        // Assert values
        $this->assertEquals($conversation->id, $array['id']);
        $this->assertEquals($booking->id, $array['booking_id']);
        $this->assertCount(2, $array['participants']);

        // Assert participants structure
        $this->assertArrayHasKey('id', $array['participants'][0]);
        $this->assertArrayHasKey('name', $array['participants'][0]);
        $this->assertArrayHasKey('avatar', $array['participants'][0]);

        // Assert timestamp format (ISO 8601 with Z)
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            $array['created_at']
        );
    }

    public function test_conversation_resource_handles_null_participant_fields(): void
    {
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultant = Consultant::factory()->create();
        
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
        ]);
        
        $conversation = Conversation::factory()->create([
            'booking_id' => $booking->id,
        ]);

        // Create user with minimal name (will result in name accessor returning just first name)
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => '',
            'avatar' => null
        ]);
        $conversation->participants()->attach($user->id);

        $resource = new ConversationResource($conversation->load('participants'));
        $array = $resource->toArray(request());

        // Find the participant we just added
        $participant = collect($array['participants'])->firstWhere('id', $user->id);
        
        $this->assertNotNull($participant);
        $this->assertEquals('John', $participant['name']);
        $this->assertNull($participant['avatar']);
    }
}
