<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Integration Tests for Ratings Auto-Update System Complete Flow
 * 
 * These tests verify the end-to-end functionality of the ratings auto-update system,
 * testing the complete flow from review creation to ratings updates.
 * 
 * @validates Requirements 8.1, 8.4, 8.5 from ratings-auto-update spec
 */
class RatingsAutoUpdateIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_creates_review_from_booking_with_consultant_service_and_updates_ratings()
    {
        // Test creating review from booking with ConsultantService
        // **Validates: Requirements 8.1, 8.4**
        
        // Arrange: Create test data
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Create booking with ConsultantService as bookable
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Act: Create review
        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 4,
            'comment' => 'Great service!',
        ]);

        // Assert: Review was created with correct consultant_service_id
        $this->assertNotNull($review->consultant_service_id);
        $this->assertEquals($service->id, $review->consultant_service_id);
        
        // Assert: Consultant ratings were updated
        $consultant->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(1, $consultant->ratings_count);
        
        // Assert: Service ratings were updated
        $service->refresh();
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
    }

    /** @test */
    public function it_creates_review_from_booking_with_consultant_directly_and_updates_only_consultant_ratings()
    {
        // Test creating review from booking with Consultant directly
        // **Validates: Requirements 8.1, 8.5**
        
        // Arrange: Create test data
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Create booking with Consultant as bookable (direct booking)
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Act: Create review
        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Excellent consultant!',
        ]);

        // Assert: Review was created without consultant_service_id
        $this->assertNull($review->consultant_service_id);
        
        // Assert: Consultant ratings were updated
        $consultant->refresh();
        $this->assertEquals(5.0, (float) $consultant->rating_avg);
        $this->assertEquals(1, $consultant->ratings_count);
        
        // Assert: Service ratings were NOT updated
        $service->refresh();
        $this->assertEquals(0.0, (float) $service->rating_avg);
        $this->assertEquals(0, $service->ratings_count);
    }

    /** @test */
    public function it_updates_review_rating_and_recalculates_ratings_correctly()
    {
        // Test updating review rating
        // **Validates: Requirements 8.1, 8.4**
        
        // Arrange: Create test data with multiple reviews
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Create first review
        $booking1 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        $review1 = Review::create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 3,
            'comment' => 'Good',
        ]);
        
        // Create second review
        $booking2 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        $review2 = Review::create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Excellent',
        ]);
        
        // Verify initial ratings (average of 3 and 5 = 4.0)
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(2, $service->ratings_count);

        // Act: Update first review rating from 3 to 5
        $review1->update(['rating' => 5]);

        // Assert: Ratings were recalculated (average of 5 and 5 = 5.0)
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(5.0, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(5.0, (float) $service->rating_avg);
        $this->assertEquals(2, $service->ratings_count);
    }

    /** @test */
    public function it_deletes_and_restores_review_and_updates_ratings_at_each_step()
    {
        // Test deleting and restoring review
        // **Validates: Requirements 8.1, 8.4**
        
        // Arrange: Create test data with multiple reviews
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Create three reviews
        $reviews = [];
        for ($i = 0; $i < 3; $i++) {
            $booking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            
            $reviews[] = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => $service->id,
                'client_id' => $client->id,
                'rating' => 4,
                'comment' => 'Good service',
            ]);
        }
        
        // Verify initial ratings (average of 4, 4, 4 = 4.0)
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(3, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(3, $service->ratings_count);

        // Act: Delete one review
        $reviews[0]->delete();

        // Assert: Ratings were recalculated after delete (average of 4, 4 = 4.0, count = 2)
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(2, $service->ratings_count);

        // Act: Restore the deleted review
        $reviews[0]->restore();

        // Assert: Ratings were recalculated after restore (average of 4, 4, 4 = 4.0, count = 3)
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(3, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(3, $service->ratings_count);
    }

    /** @test */
    public function it_verifies_ratings_are_correct_at_each_step_of_complex_flow()
    {
        // Test complete flow with multiple operations
        // **Validates: Requirements 8.1, 8.4, 8.5**
        
        // Arrange: Create test data
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Step 1: Create first review with service
        $booking1 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        $review1 = Review::create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Excellent',
        ]);
        
        // Verify: Both consultant and service have rating 5.0
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(5.0, (float) $consultant->rating_avg);
        $this->assertEquals(1, $consultant->ratings_count);
        $this->assertEquals(5.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
        
        // Step 2: Create second review without service (direct consultant booking)
        $booking2 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        $review2 = Review::create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 3,
            'comment' => 'Good',
        ]);
        
        // Verify: Consultant has average of 5 and 3 = 4.0, service still has 5.0
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(5.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
        
        // Step 3: Update first review rating
        $review1->update(['rating' => 4]);
        
        // Verify: Consultant has average of 4 and 3 = 3.5, service has 4.0
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(3.5, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
        
        // Step 4: Delete second review (direct consultant booking)
        $review2->delete();
        
        // Verify: Consultant has only first review = 4.0, service still has 4.0
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(1, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
        
        // Step 5: Restore second review
        $review2->restore();
        
        // Verify: Back to average of 4 and 3 = 3.5
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(3.5, (float) $consultant->rating_avg);
        $this->assertEquals(2, $consultant->ratings_count);
        $this->assertEquals(4.0, (float) $service->rating_avg);
        $this->assertEquals(1, $service->ratings_count);
        
        // Step 6: Delete first review (with service)
        $review1->delete();
        
        // Verify: Consultant has only second review = 3.0, service has no reviews = 0.0
        $consultant->refresh();
        $service->refresh();
        $this->assertEquals(3.0, (float) $consultant->rating_avg);
        $this->assertEquals(1, $consultant->ratings_count);
        $this->assertEquals(0.0, (float) $service->rating_avg);
        $this->assertEquals(0, $service->ratings_count);
    }

    /** @test */
    public function it_handles_mixed_service_and_direct_bookings_correctly()
    {
        // Test handling both service-specific and direct consultant bookings
        // **Validates: Requirements 8.1, 8.4, 8.5**
        
        // Arrange: Create test data
        $client = User::factory()->create(['user_type' => 'customer']);
        $consultantUser = User::factory()->create(['user_type' => 'consultant']);
        $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
        $service1 = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $service2 = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        
        // Create review for service1
        $booking1 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service1->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        Review::create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Great service 1',
        ]);
        
        // Create review for service2
        $booking2 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service2->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        Review::create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 3,
            'comment' => 'Good service 2',
        ]);
        
        // Create review for direct consultant booking
        $booking3 = Booking::factory()->create([
            'client_id' => $client->id,
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        
        Review::create([
            'booking_id' => $booking3->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 4,
            'comment' => 'Good consultant',
        ]);
        
        // Assert: Consultant has average of all three reviews (5 + 3 + 4) / 3 = 4.0
        $consultant->refresh();
        $this->assertEquals(4.0, (float) $consultant->rating_avg);
        $this->assertEquals(3, $consultant->ratings_count);
        
        // Assert: Service1 has only its review = 5.0
        $service1->refresh();
        $this->assertEquals(5.0, (float) $service1->rating_avg);
        $this->assertEquals(1, $service1->ratings_count);
        
        // Assert: Service2 has only its review = 3.0
        $service2->refresh();
        $this->assertEquals(3.0, (float) $service2->rating_avg);
        $this->assertEquals(1, $service2->ratings_count);
    }
}
