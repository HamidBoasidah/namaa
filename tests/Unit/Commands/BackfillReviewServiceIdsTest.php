<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\BackfillReviewServiceIds;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for BackfillReviewServiceIds command
 * 
 * Feature: ratings-auto-update
 * 
 * @validates Requirements 8.3
 */
class BackfillReviewServiceIdsTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Backfill Correctness Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that backfill command sets correct service IDs for service bookings
     * 
     * Verifies that reviews created from bookings with ConsultantService as bookable
     * get their consultant_service_id set correctly.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_sets_correct_service_ids(): void
    {
        // Create consultant and service
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $client = User::factory()->create();

        // Create booking with ConsultantService as bookable
        $booking = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Create review without consultant_service_id (simulating old data)
        // Use DB::table to bypass the boot method that auto-sets consultant_service_id
        $reviewId = \DB::table('reviews')->insertGetId([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Test review',
            'consultant_service_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $review = Review::find($reviewId);

        // Verify consultant_service_id is null before backfill
        $this->assertNull($review->consultant_service_id);

        // Run backfill command
        $exitCode = Artisan::call('reviews:backfill-service-ids');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify consultant_service_id was set correctly
        $review->refresh();
        $this->assertEquals($service->id, $review->consultant_service_id);
    }

    /**
     * Test that backfill command leaves consultant_service_id null for direct consultant bookings
     * 
     * Verifies that reviews from bookings with Consultant as bookable
     * keep their consultant_service_id as null.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_leaves_null_for_direct_consultant_bookings(): void
    {
        // Create consultant and client
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();

        // Create booking with Consultant as bookable (direct booking)
        $booking = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Create review without consultant_service_id
        // Use DB::table to bypass the boot method
        $reviewId = \DB::table('reviews')->insertGetId([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 4,
            'comment' => 'Test review',
            'consultant_service_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $review = Review::find($reviewId);

        // Run backfill command
        $exitCode = Artisan::call('reviews:backfill-service-ids');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify consultant_service_id remains null
        $review->refresh();
        $this->assertNull($review->consultant_service_id);
    }

    /**
     * Test that backfill command handles reviews without bookings
     * 
     * Verifies that reviews without associated bookings are skipped gracefully.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_handles_reviews_without_bookings(): void
    {
        // Create consultant and client
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();

        // Create review without booking (edge case) - booking_id is required, so skip this test
        // This is an edge case that shouldn't happen in production
        $this->markTestSkipped('Reviews require booking_id in database schema');
    }

    // ─────────────────────────────────────────────────────────────
    // Performance and Chunking Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that backfill command handles large datasets efficiently using chunking
     * 
     * Verifies that the command processes multiple reviews
     * and updates all of them correctly.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_handles_large_datasets_with_chunking(): void
    {
        // Create consultant and service
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $client = User::factory()->create();

        // Create multiple bookings and reviews (simulating large dataset)
        $reviewCount = 15;
        $bookingIds = [];

        for ($i = 0; $i < $reviewCount; $i++) {
            $booking = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Use DB::table to bypass the boot method
            \DB::table('reviews')->insert([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 5,
                'comment' => 'Test review',
                'consultant_service_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bookingIds[] = $booking->id;
        }

        // Verify reviews start with null consultant_service_id
        $nullReviews = Review::whereIn('booking_id', $bookingIds)
            ->whereNull('consultant_service_id')
            ->count();
        $this->assertEquals($reviewCount, $nullReviews);

        // Run backfill command with default chunk size
        $exitCode = Artisan::call('reviews:backfill-service-ids');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify all reviews were updated
        $updatedReviews = Review::whereIn('booking_id', $bookingIds)
            ->where('consultant_service_id', $service->id)
            ->count();
        $this->assertEquals($reviewCount, $updatedReviews, 
            "Expected all {$reviewCount} reviews to be updated, but only {$updatedReviews} were updated");
    }

    /**
     * Test that backfill command reports correct statistics
     * 
     * Verifies that the command outputs the correct number of processed
     * and updated reviews.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_reports_correct_statistics(): void
    {
        // Create consultant, service, and client
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $client = User::factory()->create();

        // Create 3 reviews with service bookings
        for ($i = 0; $i < 3; $i++) {
            $booking = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Use DB::table to bypass the boot method
            \DB::table('reviews')->insert([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 5,
                'comment' => 'Test review',
                'consultant_service_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create 2 reviews with direct consultant bookings
        for ($i = 0; $i < 2; $i++) {
            $booking = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'bookable_type' => Consultant::class,
                'bookable_id' => $consultant->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);

            // Use DB::table to bypass the boot method
            \DB::table('reviews')->insert([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 4,
                'comment' => 'Test review',
                'consultant_service_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Run backfill command
        Artisan::call('reviews:backfill-service-ids');
        $output = Artisan::output();

        // Verify output contains correct statistics
        $this->assertStringContainsString('5', $output); // 5 reviews processed
        $this->assertStringContainsString('3', $output); // 3 reviews updated
    }

    /**
     * Test that backfill command handles empty dataset gracefully
     * 
     * Verifies that the command completes successfully when there are
     * no reviews to process.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_handles_empty_dataset(): void
    {
        // Don't create any reviews

        // Run backfill command
        $exitCode = Artisan::call('reviews:backfill-service-ids');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify output indicates no reviews to process
        $output = Artisan::output();
        $this->assertStringContainsString('لا توجد تقييمات', $output);
    }

    /**
     * Test that backfill command logs successful completion
     * 
     * Verifies that the command logs statistics when it completes successfully.
     * 
     * @validates Requirements 8.3
     */
    public function test_backfill_logs_successful_completion(): void
    {
        // Create a review that needs backfilling
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
        ]);
        $client = User::factory()->create();

        $booking = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Use DB::table to bypass the boot method
        \DB::table('reviews')->insert([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Test review',
            'consultant_service_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mock Log facade
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Backfilled review service IDs'
                    && isset($context['total_processed'])
                    && isset($context['total_updated'])
                    && $context['total_processed'] === 1
                    && $context['total_updated'] === 1;
            });

        // Run backfill command
        Artisan::call('reviews:backfill-service-ids');
    }
}
