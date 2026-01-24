<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Review Model
 * 
 * Tests the consultantService relationship functionality
 */
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test consultantService relationship loads correctly
     * 
     * When a review has a consultant_service_id, the consultantService
     * relationship should load the correct ConsultantService model.
     * 
     * **Validates: Requirements 5.3**
     */
    public function test_consultant_service_relationship_loads_correctly(): void
    {
        // Create a consultant and service
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        
        // Create a booking for the service
        $client = User::factory()->create(['user_type' => 'customer']);
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
        ]);
        
        // Create a review with consultant_service_id
        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Great service!',
        ]);
        
        // Load the relationship
        $loadedService = $review->consultantService;
        
        // Assert the relationship loaded correctly
        $this->assertNotNull($loadedService);
        $this->assertInstanceOf(ConsultantService::class, $loadedService);
        $this->assertEquals($service->id, $loadedService->id);
        $this->assertEquals($service->title, $loadedService->title);
        $this->assertEquals($consultant->id, $loadedService->consultant_id);
    }

    /**
     * Test null consultant_service_id is handled correctly
     * 
     * When a review has null consultant_service_id (direct consultant booking),
     * the consultantService relationship should return null without errors.
     * 
     * **Validates: Requirements 5.5**
     */
    public function test_null_consultant_service_id_is_handled(): void
    {
        // Create a consultant
        $consultant = Consultant::factory()->create();
        
        // Create a booking directly with consultant (not a service)
        $client = User::factory()->create(['user_type' => 'customer']);
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);
        
        // Create a review without consultant_service_id
        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => null,
            'client_id' => $client->id,
            'rating' => 4,
            'comment' => 'Good consultation!',
        ]);
        
        // Load the relationship
        $loadedService = $review->consultantService;
        
        // Assert the relationship returns null without errors
        $this->assertNull($loadedService);
        $this->assertNull($review->consultant_service_id);
    }

    /**
     * Test consultantService relationship can be eager loaded
     * 
     * The consultantService relationship should support eager loading
     * to avoid N+1 query problems.
     * 
     * **Validates: Requirements 5.3**
     */
    public function test_consultant_service_relationship_can_be_eager_loaded(): void
    {
        // Create a consultant and service
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        
        // Create multiple reviews with the service
        $client = User::factory()->create(['user_type' => 'customer']);
        $reviews = [];
        
        for ($i = 0; $i < 3; $i++) {
            $booking = Booking::factory()->completed()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
            ]);
            
            $reviews[] = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => $service->id,
                'client_id' => $client->id,
                'rating' => 5,
                'comment' => "Review $i",
            ]);
        }
        
        // Eager load the consultantService relationship
        $loadedReviews = Review::with('consultantService')
            ->whereIn('id', array_map(fn($r) => $r->id, $reviews))
            ->get();
        
        // Assert all reviews have the relationship loaded
        $this->assertCount(3, $loadedReviews);
        
        foreach ($loadedReviews as $review) {
            $this->assertNotNull($review->consultantService);
            $this->assertEquals($service->id, $review->consultantService->id);
        }
    }
}
