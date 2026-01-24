<?php

namespace Tests\Unit\Services;

use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Services\RatingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for RatingsCalculatorService
 * 
 * Feature: ratings-auto-update
 * 
 * @validates Requirements 6.1, 6.2
 */
class RatingsCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RatingsCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(RatingsCalculatorService::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Transaction Rollback Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that consultant ratings update rolls back on database error
     * 
     * Verifies that when a database error occurs during consultant ratings update,
     * the transaction is rolled back and the error is logged.
     * 
     * @validates Requirements 6.1, 6.2
     */
    public function test_consultant_ratings_update_rolls_back_on_error(): void
    {
        // Create a consultant
        $consultant = Consultant::factory()->create([
            'rating_avg' => 3.50,
            'ratings_count' => 10,
        ]);

        // Store original values
        $originalRatingAvg = $consultant->rating_avg;
        $originalRatingsCount = $consultant->ratings_count;

        // Mock DB facade to throw an exception during transaction
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Mock Log facade to verify error is logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($consultant) {
                return $message === 'Failed to update consultant ratings'
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Database error'
                    && isset($context['trace']);
            });

        // Attempt to update ratings - should throw exception
        try {
            $this->service->updateConsultantRatings($consultant->id);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Database error', $e->getMessage());
        }

        // Verify consultant ratings were not changed (rollback occurred)
        $consultant->refresh();
        $this->assertEquals($originalRatingAvg, $consultant->rating_avg);
        $this->assertEquals($originalRatingsCount, $consultant->ratings_count);
    }

    /**
     * Test that service ratings update rolls back on database error
     * 
     * Verifies that when a database error occurs during service ratings update,
     * the transaction is rolled back and the error is logged.
     * 
     * @validates Requirements 6.1, 6.2
     */
    public function test_service_ratings_update_rolls_back_on_error(): void
    {
        // Create a consultant and service
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create([
            'consultant_id' => $consultant->id,
            'rating_avg' => 4.25,
            'ratings_count' => 8,
        ]);

        // Store original values
        $originalRatingAvg = $service->rating_avg;
        $originalRatingsCount = $service->ratings_count;

        // Mock DB facade to throw an exception during transaction
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database connection lost'));

        // Mock Log facade to verify error is logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($service) {
                return $message === 'Failed to update service ratings'
                    && $context['service_id'] === $service->id
                    && $context['error'] === 'Database connection lost'
                    && isset($context['trace']);
            });

        // Attempt to update ratings - should throw exception
        try {
            $this->service->updateServiceRatings($service->id);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Database connection lost', $e->getMessage());
        }

        // Verify service ratings were not changed (rollback occurred)
        $service->refresh();
        $this->assertEquals($originalRatingAvg, $service->rating_avg);
        $this->assertEquals($originalRatingsCount, $service->ratings_count);
    }

    /**
     * Test that error details are properly logged
     * 
     * Verifies that when an error occurs, all relevant information is logged
     * including the error message and stack trace.
     * 
     * @validates Requirements 6.2
     */
    public function test_error_logging_includes_details(): void
    {
        // Create a consultant
        $consultant = Consultant::factory()->create();

        // Mock DB to throw exception
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Test error with details'));

        // Capture log call
        $logCalled = false;
        $logContext = null;

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use (&$logCalled, &$logContext, $consultant) {
                $logCalled = true;
                $logContext = $context;
                
                // Verify all required fields are present
                return $message === 'Failed to update consultant ratings'
                    && isset($context['consultant_id'])
                    && isset($context['error'])
                    && isset($context['trace'])
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Test error with details';
            });

        // Attempt update
        try {
            $this->service->updateConsultantRatings($consultant->id);
        } catch (\Exception $e) {
            // Expected
        }

        // Verify log was called
        $this->assertTrue($logCalled, 'Error should be logged');
        $this->assertNotNull($logContext, 'Log context should be captured');
    }
}
