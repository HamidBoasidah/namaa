<?php

namespace Tests\Unit\Properties;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Property-Based Tests for Ratings Auto-Update System Correctness Properties
 * 
 * These tests verify universal properties that should hold across all valid executions.
 * Each test runs multiple iterations with randomly generated data.
 * 
 * @validates Requirements from ratings-auto-update spec
 */
class RatingsAutoUpdatePropertiesTest extends TestCase
{
    use DatabaseMigrations;

    protected int $iterations = 100; // Minimum 100 iterations as per spec requirements

    // ─────────────────────────────────────────────────────────────
    // Property 6: Automatic Service ID Assignment
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_automatic_service_id_assignment()
    {
        // Property: For any review created from a booking where bookable_type is ConsultantService,
        // the review's consultant_service_id should automatically be set to the bookable's ID.
        // **Validates: Requirements 5.4**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
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

            // Create review WITHOUT explicitly setting consultant_service_id
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);

            // Assert: consultant_service_id was automatically set to the service ID
            $this->assertNotNull($review->consultant_service_id);
            $this->assertEquals($service->id, $review->consultant_service_id);
            
            // Cleanup for next iteration
            $review->delete();
            $booking->delete();
            $service->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    /** @test */
    public function property_automatic_service_id_assignment_null_for_direct_consultant_booking()
    {
        // Property: For any review created from a booking where bookable_type is Consultant,
        // the review's consultant_service_id should remain null.
        // **Validates: Requirements 5.5**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $client = User::factory()->create(['user_type' => 'customer']);
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            
            // Create booking with Consultant as bookable (direct booking)
            $booking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Create review WITHOUT explicitly setting consultant_service_id
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);

            // Assert: consultant_service_id should be null for direct consultant bookings
            $this->assertNull($review->consultant_service_id);
            
            // Cleanup for next iteration
            $review->delete();
            $booking->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    /** @test */
    public function property_automatic_service_id_assignment_respects_explicit_value()
    {
        // Property: If consultant_service_id is explicitly set, it should not be overridden
        // **Validates: Requirements 5.4 (edge case)**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $client = User::factory()->create(['user_type' => 'customer']);
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service1 = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            $service2 = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            
            // Create booking with service1 as bookable
            $booking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service1->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Create review WITH explicitly set consultant_service_id (different from booking's bookable)
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'consultant_service_id' => $service2->id, // Explicitly set to different service
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);

            // Assert: Explicitly set consultant_service_id should be preserved
            $this->assertNotNull($review->consultant_service_id);
            $this->assertEquals($service2->id, $review->consultant_service_id);
            $this->assertNotEquals($service1->id, $review->consultant_service_id);
            
            // Cleanup for next iteration
            $review->delete();
            $booking->delete();
            $service1->delete();
            $service2->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 1: Consultant Ratings Accuracy After Any Review Change
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_consultant_ratings_accuracy_after_any_review_change()
    {
        // Property: For any consultant and any sequence of review operations (create, update, delete, restore),
        // the consultant's rating_avg should equal the average of all non-deleted reviews for that consultant,
        // and ratings_count should equal the count of all non-deleted reviews.
        // **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**
        
        $ratingsCalculator = app(\App\Services\RatingsCalculatorService::class);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Generate random number of reviews (0-20)
            $reviewCount = fake()->numberBetween(0, 20);
            $reviews = [];
            
            for ($j = 0; $j < $reviewCount; $j++) {
                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'consultant_id' => $consultant->id,
                    'bookable_type' => Consultant::class,
                    'bookable_id' => $consultant->id,
                    'status' => Booking::STATUS_COMPLETED,
                ]);
                
                $reviews[] = Review::create([
                    'booking_id' => $booking->id,
                    'consultant_id' => $consultant->id,
                    'client_id' => $client->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
            }
            
            // Perform random operations (update, delete, restore) on some reviews
            if ($reviewCount > 0) {
                $operationCount = min(5, $reviewCount);
                $selectedReviews = fake()->randomElements($reviews, min($operationCount, count($reviews)));
                
                foreach ($selectedReviews as $review) {
                    $operation = fake()->randomElement(['update', 'delete', 'restore']);
                    
                    if ($operation === 'update') {
                        $review->update(['rating' => fake()->numberBetween(1, 5)]);
                    } elseif ($operation === 'delete') {
                        $review->delete();
                    } elseif ($operation === 'restore' && $review->trashed()) {
                        $review->restore();
                    }
                }
            }
            
            // Manually trigger ratings calculation
            $ratingsCalculator->updateConsultantRatings($consultant->id);
            
            // Calculate expected values (only non-deleted reviews)
            $activeReviews = Review::where('consultant_id', $consultant->id)->get();
            $expectedAvg = $activeReviews->count() > 0 ? round($activeReviews->avg('rating'), 2) : 0;
            $expectedCount = $activeReviews->count();
            
            // Refresh consultant to get updated values
            $consultant->refresh();
            
            // Assert ratings are accurate
            $this->assertEquals(
                $expectedAvg,
                (float) $consultant->rating_avg,
                "Consultant rating_avg should be {$expectedAvg}, got {$consultant->rating_avg}"
            );
            $this->assertEquals(
                $expectedCount,
                $consultant->ratings_count,
                "Consultant ratings_count should be {$expectedCount}, got {$consultant->ratings_count}"
            );
            
            // Cleanup
            foreach ($reviews as $review) {
                $review->forceDelete();
            }
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 2: Service Ratings Accuracy After Any Review Change
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_service_ratings_accuracy_after_any_review_change()
    {
        // Property: For any consultant service and any sequence of review operations (create, update, delete, restore)
        // on reviews with consultant_service_id, the service's rating_avg should equal the average of all non-deleted
        // reviews for that service, and ratings_count should equal the count of all non-deleted reviews.
        // **Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.5, 4.6**
        
        $ratingsCalculator = app(\App\Services\RatingsCalculatorService::class);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Generate random number of reviews (0-20)
            $reviewCount = fake()->numberBetween(0, 20);
            $reviews = [];
            
            for ($j = 0; $j < $reviewCount; $j++) {
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
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
            }
            
            // Perform random operations (update, delete, restore) on some reviews
            if ($reviewCount > 0) {
                $operationCount = min(5, $reviewCount);
                $selectedReviews = fake()->randomElements($reviews, min($operationCount, count($reviews)));
                
                foreach ($selectedReviews as $review) {
                    $operation = fake()->randomElement(['update', 'delete', 'restore']);
                    
                    if ($operation === 'update') {
                        $review->update(['rating' => fake()->numberBetween(1, 5)]);
                    } elseif ($operation === 'delete') {
                        $review->delete();
                    } elseif ($operation === 'restore' && $review->trashed()) {
                        $review->restore();
                    }
                }
            }
            
            // Manually trigger ratings calculation
            $ratingsCalculator->updateServiceRatings($service->id);
            
            // Calculate expected values (only non-deleted reviews)
            $activeReviews = Review::where('consultant_service_id', $service->id)->get();
            $expectedAvg = $activeReviews->count() > 0 ? round($activeReviews->avg('rating'), 2) : 0;
            $expectedCount = $activeReviews->count();
            
            // Refresh service to get updated values
            $service->refresh();
            
            // Assert ratings are accurate
            $this->assertEquals(
                $expectedAvg,
                (float) $service->rating_avg,
                "Service rating_avg should be {$expectedAvg}, got {$service->rating_avg}"
            );
            $this->assertEquals(
                $expectedCount,
                $service->ratings_count,
                "Service ratings_count should be {$expectedCount}, got {$service->ratings_count}"
            );
            
            // Cleanup
            foreach ($reviews as $review) {
                $review->forceDelete();
            }
            $service->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 3: Soft-Deleted Reviews Exclusion
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_soft_deleted_reviews_exclusion()
    {
        // Property: For any consultant or service, when calculating ratings, all soft-deleted reviews
        // should be excluded from both the average calculation and the count.
        // **Validates: Requirements 6.6**
        
        $ratingsCalculator = app(\App\Services\RatingsCalculatorService::class);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Create a mix of active and soft-deleted reviews
            $activeReviewCount = fake()->numberBetween(1, 10);
            $deletedReviewCount = fake()->numberBetween(1, 10);
            
            $activeReviews = [];
            $deletedReviews = [];
            
            // Create active reviews
            for ($j = 0; $j < $activeReviewCount; $j++) {
                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'consultant_id' => $consultant->id,
                    'bookable_type' => ConsultantService::class,
                    'bookable_id' => $service->id,
                    'status' => Booking::STATUS_COMPLETED,
                ]);
                
                $activeReviews[] = Review::create([
                    'booking_id' => $booking->id,
                    'consultant_id' => $consultant->id,
                    'consultant_service_id' => $service->id,
                    'client_id' => $client->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
            }
            
            // Create soft-deleted reviews
            for ($j = 0; $j < $deletedReviewCount; $j++) {
                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'consultant_id' => $consultant->id,
                    'bookable_type' => ConsultantService::class,
                    'bookable_id' => $service->id,
                    'status' => Booking::STATUS_COMPLETED,
                ]);
                
                $review = Review::create([
                    'booking_id' => $booking->id,
                    'consultant_id' => $consultant->id,
                    'consultant_service_id' => $service->id,
                    'client_id' => $client->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
                
                $review->delete(); // Soft delete
                $deletedReviews[] = $review;
            }
            
            // Update ratings for both consultant and service
            $ratingsCalculator->updateConsultantRatings($consultant->id);
            $ratingsCalculator->updateServiceRatings($service->id);
            
            // Calculate expected values (only active reviews, excluding soft-deleted)
            $expectedAvg = count($activeReviews) > 0 
                ? round(collect($activeReviews)->avg('rating'), 2) 
                : 0;
            $expectedCount = count($activeReviews);
            
            // Refresh models
            $consultant->refresh();
            $service->refresh();
            
            // Assert consultant ratings exclude soft-deleted reviews
            $this->assertEquals(
                $expectedAvg,
                (float) $consultant->rating_avg,
                "Consultant rating_avg should exclude soft-deleted reviews. Expected {$expectedAvg}, got {$consultant->rating_avg}"
            );
            $this->assertEquals(
                $expectedCount,
                $consultant->ratings_count,
                "Consultant ratings_count should exclude soft-deleted reviews. Expected {$expectedCount}, got {$consultant->ratings_count}"
            );
            
            // Assert service ratings exclude soft-deleted reviews
            $this->assertEquals(
                $expectedAvg,
                (float) $service->rating_avg,
                "Service rating_avg should exclude soft-deleted reviews. Expected {$expectedAvg}, got {$service->rating_avg}"
            );
            $this->assertEquals(
                $expectedCount,
                $service->ratings_count,
                "Service ratings_count should exclude soft-deleted reviews. Expected {$expectedCount}, got {$service->ratings_count}"
            );
            
            // Verify soft-deleted reviews are actually in database but excluded
            $allReviewsIncludingDeleted = Review::withTrashed()
                ->where('consultant_id', $consultant->id)
                ->count();
            $this->assertEquals(
                $activeReviewCount + $deletedReviewCount,
                $allReviewsIncludingDeleted,
                "All reviews (active + deleted) should exist in database"
            );
            
            // Cleanup
            foreach ($activeReviews as $review) {
                $review->forceDelete();
            }
            foreach ($deletedReviews as $review) {
                $review->forceDelete();
            }
            $service->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 4: Zero Ratings for Empty Review Set
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_zero_ratings_for_empty_review_set()
    {
        // Property: For any consultant or service with no non-deleted reviews,
        // the rating_avg should be 0 and ratings_count should be 0.
        // **Validates: Requirements 3.7, 4.7**
        
        $ratingsCalculator = app(\App\Services\RatingsCalculatorService::class);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            
            // Test Case 1: No reviews at all
            $ratingsCalculator->updateConsultantRatings($consultant->id);
            $ratingsCalculator->updateServiceRatings($service->id);
            
            $consultant->refresh();
            $service->refresh();
            
            $this->assertEquals(0, (float) $consultant->rating_avg, "Consultant with no reviews should have rating_avg = 0");
            $this->assertEquals(0, $consultant->ratings_count, "Consultant with no reviews should have ratings_count = 0");
            $this->assertEquals(0, (float) $service->rating_avg, "Service with no reviews should have rating_avg = 0");
            $this->assertEquals(0, $service->ratings_count, "Service with no reviews should have ratings_count = 0");
            
            // Test Case 2: All reviews are soft-deleted
            $client = User::factory()->create(['user_type' => 'customer']);
            $reviewCount = fake()->numberBetween(1, 5);
            $reviews = [];
            
            for ($j = 0; $j < $reviewCount; $j++) {
                $booking = Booking::factory()->create([
                    'client_id' => $client->id,
                    'consultant_id' => $consultant->id,
                    'bookable_type' => ConsultantService::class,
                    'bookable_id' => $service->id,
                    'status' => Booking::STATUS_COMPLETED,
                ]);
                
                $review = Review::create([
                    'booking_id' => $booking->id,
                    'consultant_id' => $consultant->id,
                    'consultant_service_id' => $service->id,
                    'client_id' => $client->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
                
                $review->delete(); // Soft delete immediately
                $reviews[] = $review;
            }
            
            // Update ratings after all reviews are deleted
            $ratingsCalculator->updateConsultantRatings($consultant->id);
            $ratingsCalculator->updateServiceRatings($service->id);
            
            $consultant->refresh();
            $service->refresh();
            
            $this->assertEquals(0, (float) $consultant->rating_avg, "Consultant with all deleted reviews should have rating_avg = 0");
            $this->assertEquals(0, $consultant->ratings_count, "Consultant with all deleted reviews should have ratings_count = 0");
            $this->assertEquals(0, (float) $service->rating_avg, "Service with all deleted reviews should have rating_avg = 0");
            $this->assertEquals(0, $service->ratings_count, "Service with all deleted reviews should have ratings_count = 0");
            
            // Cleanup
            foreach ($reviews as $review) {
                $review->forceDelete();
            }
            $service->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 5: Service-Specific Review Handling
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_service_specific_review_handling()
    {
        // Property: For any review with consultant_service_id set, both the consultant's ratings
        // and the service's ratings should be updated. For any review without consultant_service_id,
        // only the consultant's ratings should be updated.
        // **Validates: Requirements 4.8, 5.4**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Scenario 1: Create review WITH consultant_service_id
            $bookingWithService = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            
            $reviewWithService = Review::create([
                'booking_id' => $bookingWithService->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => $service->id,
                'client_id' => $client->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Refresh to get updated ratings
            $consultant->refresh();
            $service->refresh();
            
            // Assert: Both consultant and service ratings should be updated
            $this->assertEquals(
                $reviewWithService->rating,
                (float) $consultant->rating_avg,
                "Consultant rating should be updated when review has consultant_service_id"
            );
            $this->assertEquals(
                1,
                $consultant->ratings_count,
                "Consultant ratings_count should be 1"
            );
            $this->assertEquals(
                $reviewWithService->rating,
                (float) $service->rating_avg,
                "Service rating should be updated when review has consultant_service_id"
            );
            $this->assertEquals(
                1,
                $service->ratings_count,
                "Service ratings_count should be 1"
            );
            
            // Store service ratings before next test
            $serviceRatingBefore = $service->rating_avg;
            $serviceCountBefore = $service->ratings_count;
            
            // Scenario 2: Create review WITHOUT consultant_service_id (direct consultant booking)
            $bookingWithoutService = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            
            $reviewWithoutService = Review::create([
                'booking_id' => $bookingWithoutService->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => null, // Explicitly null
                'client_id' => $client->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);
            
            // Refresh to get updated ratings
            $consultant->refresh();
            $service->refresh();
            
            // Assert: Only consultant ratings should be updated, service ratings should remain unchanged
            $expectedConsultantAvg = round(($reviewWithService->rating + $reviewWithoutService->rating) / 2, 2);
            $this->assertEquals(
                $expectedConsultantAvg,
                (float) $consultant->rating_avg,
                "Consultant rating should include both reviews (with and without service_id)"
            );
            $this->assertEquals(
                2,
                $consultant->ratings_count,
                "Consultant ratings_count should be 2"
            );
            
            // Service ratings should NOT change after review without consultant_service_id
            $this->assertEquals(
                $serviceRatingBefore,
                $service->rating_avg,
                "Service rating should NOT change when review has no consultant_service_id"
            );
            $this->assertEquals(
                $serviceCountBefore,
                $service->ratings_count,
                "Service ratings_count should NOT change when review has no consultant_service_id"
            );
            
            // Cleanup
            $reviewWithService->forceDelete();
            $reviewWithoutService->forceDelete();
            $service->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 7: Backward Compatibility with Null Service ID
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_backward_compatibility_with_null_service_id()
    {
        // Property: For any review without consultant_service_id (null), the review should function
        // correctly and only update consultant ratings without errors.
        // **Validates: Requirements 8.2, 8.5**
        
        $ratingsCalculator = app(\App\Services\RatingsCalculatorService::class);
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Create booking with Consultant as bookable (simulating old system behavior)
            $booking = Booking::factory()->create([
                'client_id' => $client->id,
                'consultant_id' => $consultant->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Create review with null consultant_service_id (backward compatibility scenario)
            $review = Review::create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => null,
                'client_id' => $client->id,
                'rating' => fake()->numberBetween(1, 5),
                'comment' => fake()->optional()->sentence(),
            ]);

            // Assert: Review was created successfully
            $this->assertNotNull($review->id);
            $this->assertNull($review->consultant_service_id);
            
            // Refresh consultant to get updated ratings
            $consultant->refresh();
            
            // Assert: Consultant ratings were updated correctly
            $this->assertEquals(
                $review->rating,
                (float) $consultant->rating_avg,
                "Consultant rating should be updated even when consultant_service_id is null"
            );
            $this->assertEquals(
                1,
                $consultant->ratings_count,
                "Consultant ratings_count should be 1"
            );
            
            // Test update operation with null service_id
            $newRating = fake()->numberBetween(1, 5);
            $review->update(['rating' => $newRating]);
            
            $consultant->refresh();
            
            // Assert: Update worked correctly
            $this->assertEquals(
                $newRating,
                (float) $consultant->rating_avg,
                "Consultant rating should update correctly when consultant_service_id is null"
            );
            
            // Test delete operation with null service_id
            $review->delete();
            
            $consultant->refresh();
            
            // Assert: Delete worked correctly
            $this->assertEquals(
                0,
                (float) $consultant->rating_avg,
                "Consultant rating should be 0 after deleting the only review"
            );
            $this->assertEquals(
                0,
                $consultant->ratings_count,
                "Consultant ratings_count should be 0 after deleting the only review"
            );
            
            // Test restore operation with null service_id
            $review->restore();
            
            $consultant->refresh();
            
            // Assert: Restore worked correctly
            $this->assertEquals(
                $newRating,
                (float) $consultant->rating_avg,
                "Consultant rating should be restored correctly when consultant_service_id is null"
            );
            $this->assertEquals(
                1,
                $consultant->ratings_count,
                "Consultant ratings_count should be 1 after restore"
            );
            
            // Cleanup
            $review->forceDelete();
            $booking->delete();
            $consultant->delete();
            $consultantUser->delete();
            $client->delete();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Property 8: Soft-Delete Preservation
    // ─────────────────────────────────────────────────────────────

    /** @test */
    public function property_soft_delete_preservation()
    {
        // Property: For any consultant or service that is soft-deleted, their rating_avg and
        // ratings_count values should remain unchanged and preserved.
        // **Validates: Requirements 6.4, 6.5**
        
        for ($i = 0; $i < $this->iterations; $i++) {
            // Create test data
            $consultantUser = User::factory()->create(['user_type' => 'consultant']);
            $consultant = Consultant::factory()->create(['user_id' => $consultantUser->id]);
            $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
            $client = User::factory()->create(['user_type' => 'customer']);
            
            // Create random number of reviews
            $reviewCount = fake()->numberBetween(1, 10);
            $reviews = [];
            
            for ($j = 0; $j < $reviewCount; $j++) {
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
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->optional()->sentence(),
                ]);
            }
            
            // Refresh to get current ratings
            $consultant->refresh();
            $service->refresh();
            
            // Store ratings before soft-delete
            $consultantRatingBefore = $consultant->rating_avg;
            $consultantCountBefore = $consultant->ratings_count;
            $serviceRatingBefore = $service->rating_avg;
            $serviceCountBefore = $service->ratings_count;
            
            // Assert ratings are not zero (we have reviews)
            $this->assertGreaterThan(0, $consultantRatingBefore);
            $this->assertGreaterThan(0, $consultantCountBefore);
            $this->assertGreaterThan(0, $serviceRatingBefore);
            $this->assertGreaterThan(0, $serviceCountBefore);
            
            // Soft-delete the consultant
            $consultant->delete();
            
            // Retrieve consultant with trashed
            $deletedConsultant = Consultant::withTrashed()->find($consultant->id);
            
            // Assert: Consultant ratings are preserved after soft-delete
            $this->assertNotNull($deletedConsultant->deleted_at, "Consultant should be soft-deleted");
            $this->assertEquals(
                $consultantRatingBefore,
                $deletedConsultant->rating_avg,
                "Consultant rating_avg should be preserved after soft-delete"
            );
            $this->assertEquals(
                $consultantCountBefore,
                $deletedConsultant->ratings_count,
                "Consultant ratings_count should be preserved after soft-delete"
            );
            
            // Restore consultant for service test
            $deletedConsultant->restore();
            
            // Soft-delete the service
            $service->delete();
            
            // Retrieve service with trashed
            $deletedService = ConsultantService::withTrashed()->find($service->id);
            
            // Assert: Service ratings are preserved after soft-delete
            $this->assertNotNull($deletedService->deleted_at, "Service should be soft-deleted");
            $this->assertEquals(
                $serviceRatingBefore,
                $deletedService->rating_avg,
                "Service rating_avg should be preserved after soft-delete"
            );
            $this->assertEquals(
                $serviceCountBefore,
                $deletedService->ratings_count,
                "Service ratings_count should be preserved after soft-delete"
            );
            
            // Cleanup
            foreach ($reviews as $review) {
                $review->forceDelete();
            }
            $deletedService->forceDelete();
            $consultant->forceDelete();
            $consultantUser->delete();
            $client->delete();
        }
    }
}
