<?php

namespace Tests\Unit\Migrations;

use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration integrity tests for Ratings Auto-Update System
 * 
 * Tests that the database schema changes for the ratings auto-update feature
 * are correctly applied, including column types, foreign keys, and cascade behavior.
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
 */
class RatingsAutoUpdateMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that rating_avg column exists in consultant_services table with correct type
     * 
     * **Validates: Requirement 1.1**
     */
    public function test_consultant_services_has_rating_avg_column_with_correct_type(): void
    {
        $this->assertTrue(
            Schema::hasColumn('consultant_services', 'rating_avg'),
            'consultant_services table should have rating_avg column'
        );

        // Create a service and verify the column accepts decimal values
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'rating_avg' => 4.75,
        ]);

        $this->assertEquals(4.75, $service->rating_avg);
        
        // Verify default value is 0
        $serviceWithDefault = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $this->assertEquals(0, $serviceWithDefault->rating_avg);
    }

    /**
     * Test that ratings_count column exists in consultant_services table with correct type
     * 
     * **Validates: Requirement 1.2**
     */
    public function test_consultant_services_has_ratings_count_column_with_correct_type(): void
    {
        $this->assertTrue(
            Schema::hasColumn('consultant_services', 'ratings_count'),
            'consultant_services table should have ratings_count column'
        );

        // Create a service and verify the column accepts unsigned integer values
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'ratings_count' => 150,
        ]);

        $this->assertEquals(150, $service->ratings_count);
        
        // Verify default value is 0
        $serviceWithDefault = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $this->assertEquals(0, $serviceWithDefault->ratings_count);
    }

    /**
     * Test that rating_avg column has an index for query performance
     * 
     * **Validates: Requirement 1.5**
     */
    public function test_consultant_services_rating_avg_has_index(): void
    {
        $indexes = Schema::getIndexes('consultant_services');
        
        $hasRatingAvgIndex = false;
        foreach ($indexes as $index) {
            if (in_array('rating_avg', $index['columns'])) {
                $hasRatingAvgIndex = true;
                break;
            }
        }
        
        $this->assertTrue(
            $hasRatingAvgIndex,
            'consultant_services.rating_avg should have an index'
        );
    }

    /**
     * Test that consultant_service_id column exists in reviews table
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_reviews_has_consultant_service_id_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('reviews', 'consultant_service_id'),
            'reviews table should have consultant_service_id column'
        );
    }

    /**
     * Test that consultant_service_id is nullable in reviews table
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_reviews_consultant_service_id_is_nullable(): void
    {
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'consultant_id' => $consultant->id,
        ]);

        // Create a review without consultant_service_id
        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $user->id,
            'consultant_service_id' => null,
        ]);

        $this->assertNull($review->consultant_service_id);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'consultant_service_id' => null,
        ]);
    }

    /**
     * Test that consultant_service_id foreign key constraint works
     * 
     * **Validates: Requirement 1.3**
     */
    public function test_reviews_consultant_service_id_foreign_key_constraint(): void
    {
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'consultant_id' => $consultant->id,
        ]);

        // Create a review with valid consultant_service_id
        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $user->id,
            'consultant_service_id' => $service->id,
        ]);

        $this->assertEquals($service->id, $review->consultant_service_id);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'consultant_service_id' => $service->id,
        ]);
    }

    /**
     * Test that cascade on delete works for consultant_service_id
     * 
     * When a ConsultantService is deleted, all associated reviews should also be deleted.
     * 
     * **Validates: Requirement 1.4**
     */
    public function test_reviews_cascade_on_delete_when_consultant_service_deleted(): void
    {
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'consultant_id' => $consultant->id,
        ]);

        // Create a review linked to the service
        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $user->id,
            'consultant_service_id' => $service->id,
        ]);

        $reviewId = $review->id;

        // Force delete the service (bypass soft delete to trigger cascade)
        $service->forceDelete();

        // Verify the review was also deleted (cascade)
        $this->assertDatabaseMissing('reviews', [
            'id' => $reviewId,
        ]);
    }

    /**
     * Test that reviews without consultant_service_id are not affected by service deletion
     * 
     * **Validates: Requirement 1.4**
     */
    public function test_reviews_without_service_id_unaffected_by_service_deletion(): void
    {
        $user = User::factory()->create();
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $user->id,
            'consultant_id' => $consultant->id,
        ]);

        // Create a review without consultant_service_id
        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $user->id,
            'consultant_service_id' => null,
        ]);

        $reviewId = $review->id;

        // Delete the service
        $service->delete();

        // Verify the review still exists
        $this->assertDatabaseHas('reviews', [
            'id' => $reviewId,
        ]);
    }

    /**
     * Test that both rating columns can store valid rating values
     * 
     * **Validates: Requirements 1.1, 1.2**
     */
    public function test_consultant_services_rating_columns_store_valid_values(): void
    {
        $consultant = Consultant::factory()->create();
        
        // Test various valid rating values
        $testCases = [
            ['avg' => 0.00, 'count' => 0],
            ['avg' => 1.50, 'count' => 2],
            ['avg' => 3.75, 'count' => 50],
            ['avg' => 5.00, 'count' => 100],
            ['avg' => 4.33, 'count' => 999],
        ];

        foreach ($testCases as $index => $testCase) {
            $service = ConsultantService::factory()->create([
                'consultant_id' => $consultant->id,
                'title' => 'Test Service ' . $index,
                'rating_avg' => $testCase['avg'],
                'ratings_count' => $testCase['count'],
            ]);

            $this->assertEquals($testCase['avg'], $service->rating_avg);
            $this->assertEquals($testCase['count'], $service->ratings_count);
            
            // Verify persistence
            $freshService = ConsultantService::find($service->id);
            $this->assertEquals($testCase['avg'], $freshService->rating_avg);
            $this->assertEquals($testCase['count'], $freshService->ratings_count);
        }
    }
}
