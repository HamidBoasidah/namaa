<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\RecalculateAllRatings;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use App\Services\RatingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for RecalculateAllRatings command
 * 
 * Feature: ratings-auto-update
 * 
 * @validates Requirements 8.3
 */
class RecalculateAllRatingsTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // Recalculation Correctness Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that recalculate command updates all consultant ratings correctly
     * 
     * Verifies that the command recalculates ratings for all consultants
     * based on their reviews.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_updates_all_consultant_ratings(): void
    {
        // Create consultants with incorrect ratings
        $consultant1 = Consultant::factory()->create([
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);
        $consultant2 = Consultant::factory()->create([
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);

        $client = User::factory()->create();

        // Create reviews for consultant1 (each needs its own booking)
        $booking1 = Booking::factory()->create([
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);
        
        $booking2 = Booking::factory()->create([
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Create reviews for consultant2
        $booking3 = Booking::factory()->create([
            'consultant_id' => $consultant2->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking3->id,
            'consultant_id' => $consultant2->id,
            'client_id' => $client->id,
            'rating' => 3,
        ]);

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify consultant1 ratings were updated correctly
        $consultant1->refresh();
        $this->assertEquals(4.5, $consultant1->rating_avg);
        $this->assertEquals(2, $consultant1->ratings_count);

        // Verify consultant2 ratings were updated correctly
        $consultant2->refresh();
        $this->assertEquals(3.0, $consultant2->rating_avg);
        $this->assertEquals(1, $consultant2->ratings_count);
    }

    /**
     * Test that recalculate command updates all service ratings correctly
     * 
     * Verifies that the command recalculates ratings for all consultant services
     * based on their reviews.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_updates_all_service_ratings(): void
    {
        // Create consultant and services with incorrect ratings
        $consultant = Consultant::factory()->create();
        $service1 = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);
        $service2 = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);

        $client = User::factory()->create();

        // Create reviews for service1 (each needs its own booking)
        $booking1 = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service1->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service1->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);
        
        $booking2 = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service1->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service1->id,
            'client_id' => $client->id,
            'rating' => 3,
        ]);

        // Create reviews for service2
        $booking3 = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service2->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking3->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service2->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify service1 ratings were updated correctly
        $service1->refresh();
        $this->assertEquals(4.0, $service1->rating_avg);
        $this->assertEquals(2, $service1->ratings_count);

        // Verify service2 ratings were updated correctly
        $service2->refresh();
        $this->assertEquals(4.0, $service2->rating_avg);
        $this->assertEquals(1, $service2->ratings_count);
    }

    /**
     * Test that recalculate command handles consultants with no reviews
     * 
     * Verifies that consultants without reviews get their ratings set to 0.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_handles_consultants_with_no_reviews(): void
    {
        // Create consultant with incorrect ratings but no reviews
        $consultant = Consultant::factory()->create([
            'rating_avg' => 4.5,
            'ratings_count' => 10,
        ]);

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify consultant ratings were reset to 0
        $consultant->refresh();
        $this->assertEquals(0, $consultant->rating_avg);
        $this->assertEquals(0, $consultant->ratings_count);
    }

    /**
     * Test that recalculate command handles services with no reviews
     * 
     * Verifies that services without reviews get their ratings set to 0.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_handles_services_with_no_reviews(): void
    {
        // Create consultant and service with incorrect ratings but no reviews
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'rating_avg' => 3.8,
            'ratings_count' => 5,
        ]);

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify service ratings were reset to 0
        $service->refresh();
        $this->assertEquals(0, $service->rating_avg);
        $this->assertEquals(0, $service->ratings_count);
    }

    // ─────────────────────────────────────────────────────────────
    // Performance and Chunking Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that recalculate command handles large datasets efficiently using chunking
     * 
     * Verifies that the command processes multiple consultants and services
     * in chunks and updates all of them correctly.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_handles_large_datasets_with_chunking(): void
    {
        $client = User::factory()->create();

        // Create multiple consultants with reviews
        $consultantCount = 10;
        $consultants = [];

        for ($i = 0; $i < $consultantCount; $i++) {
            $consultant = Consultant::factory()->create([
                'rating_avg' => 0,
                'ratings_count' => 0,
            ]);

            // Create 2 reviews for each consultant (each needs its own booking)
            $booking1 = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            Review::factory()->create([
                'booking_id' => $booking1->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 5,
            ]);
            
            $booking2 = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            Review::factory()->create([
                'booking_id' => $booking2->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 3,
            ]);

            $consultants[] = $consultant;
        }

        // Run recalculate command with small chunk size
        $exitCode = Artisan::call('ratings:recalculate-all', ['--chunk' => 3]);

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify all consultants were updated
        foreach ($consultants as $consultant) {
            $consultant->refresh();
            $this->assertEquals(4.0, $consultant->rating_avg);
            $this->assertEquals(2, $consultant->ratings_count);
        }
    }

    /**
     * Test that recalculate command continues processing after individual errors
     * 
     * Verifies that if updating one consultant/service fails, the command
     * continues processing the rest.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_continues_after_individual_errors(): void
    {
        // Create consultants
        $consultant1 = Consultant::factory()->create([
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);
        $consultant2 = Consultant::factory()->create([
            'rating_avg' => 0,
            'ratings_count' => 0,
        ]);

        $client = User::factory()->create();

        // Create reviews (each needs its own booking)
        $booking1 = Booking::factory()->create([
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking1->id,
            'consultant_id' => $consultant1->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);
        
        $booking2 = Booking::factory()->create([
            'consultant_id' => $consultant2->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking2->id,
            'consultant_id' => $consultant2->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Mock RatingsCalculatorService to fail for consultant1 but succeed for consultant2
        $mockService = $this->mock(RatingsCalculatorService::class);
        
        $mockService->shouldReceive('updateConsultantRatings')
            ->with($consultant1->id)
            ->once()
            ->andThrow(new \Exception('Database error'));

        $mockService->shouldReceive('updateConsultantRatings')
            ->with($consultant2->id)
            ->once()
            ->andReturnNull();

        // Expect error to be logged for consultant1
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($consultant1) {
                return $message === 'Failed to update consultant ratings'
                    && $context['consultant_id'] === $consultant1->id;
            });

        // Expect info log at the end
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Recalculated all ratings';
            });

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded despite individual error
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test that recalculate command reports correct statistics
     * 
     * Verifies that the command outputs the correct number of updated
     * consultants and services.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_reports_correct_statistics(): void
    {
        $client = User::factory()->create();

        // Create 3 consultants with reviews (each needs its own booking)
        for ($i = 0; $i < 3; $i++) {
            $consultant = Consultant::factory()->create();
            $booking = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            Review::factory()->create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'rating' => 5,
            ]);
        }

        // Create 2 services with reviews (each needs its own booking)
        $consultant = Consultant::factory()->create();
        for ($i = 0; $i < 2; $i++) {
            $service = ConsultantService::factory()->create([
                'consultant_id' => $consultant->id,
            ]);
            $booking = Booking::factory()->create([
                'consultant_id' => $consultant->id,
                'client_id' => $client->id,
                'bookable_type' => ConsultantService::class,
                'bookable_id' => $service->id,
                'status' => Booking::STATUS_COMPLETED,
            ]);
            Review::factory()->create([
                'booking_id' => $booking->id,
                'consultant_id' => $consultant->id,
                'consultant_service_id' => $service->id,
                'client_id' => $client->id,
                'rating' => 4,
            ]);
        }

        // Run recalculate command
        Artisan::call('ratings:recalculate-all');
        $output = Artisan::output();

        // Verify output contains correct statistics
        $this->assertStringContainsString('4', $output); // 4 consultants (3 + 1)
        $this->assertStringContainsString('2', $output); // 2 services
    }

    /**
     * Test that recalculate command handles empty dataset gracefully
     * 
     * Verifies that the command completes successfully when there are
     * no consultants or services to process.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_handles_empty_dataset(): void
    {
        // Don't create any consultants or services

        // Run recalculate command
        $exitCode = Artisan::call('ratings:recalculate-all');

        // Verify command succeeded
        $this->assertEquals(0, $exitCode);

        // Verify output indicates completion
        $output = Artisan::output();
        $this->assertStringContainsString('0', $output);
    }

    /**
     * Test that recalculate command logs successful completion
     * 
     * Verifies that the command logs statistics when it completes successfully.
     * 
     * @validates Requirements 8.3
     */
    public function test_recalculate_logs_successful_completion(): void
    {
        // Create a consultant with a review (needs its own booking)
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();

        $booking = Booking::factory()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'status' => Booking::STATUS_COMPLETED,
        ]);
        Review::factory()->create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);

        // Mock Log facade
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Recalculated all ratings'
                    && isset($context['total_consultants'])
                    && isset($context['total_services'])
                    && $context['total_consultants'] === 1
                    && $context['total_services'] === 0;
            });

        // Run recalculate command
        Artisan::call('ratings:recalculate-all');
    }
}
