<?php

namespace Tests\Unit\Observers;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use App\Models\User;
use App\Observers\ReviewObserver;
use App\Services\RatingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for ReviewObserver
 * 
 * Feature: ratings-auto-update
 * 
 * @validates Requirements 2.3, 2.4, 2.5, 2.6
 */
class ReviewObserverTest extends TestCase
{
    use RefreshDatabase;

    protected RatingsCalculatorService $ratingsCalculator;
    protected ReviewObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ratingsCalculator = $this->createMock(RatingsCalculatorService::class);
        $this->observer = new ReviewObserver($this->ratingsCalculator);
    }

    // ─────────────────────────────────────────────────────────────
    // Created Event Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that created event triggers rating update for consultant
     * 
     * @validates Requirements 2.3
     */
    public function test_created_event_triggers_consultant_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = new Review([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
            'comment' => 'Great service!',
        ]);
        $review->id = 1;

        // Expect updateConsultantRatings to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        // Expect updateServiceRatings NOT to be called (no service)
        $this->ratingsCalculator
            ->expects($this->never())
            ->method('updateServiceRatings');

        // Trigger the created event
        $this->observer->created($review);
    }

    /**
     * Test that created event triggers rating update for both consultant and service
     * 
     * @validates Requirements 2.3
     */
    public function test_created_event_triggers_service_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
        ]);

        $review = new Review([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 4,
            'comment' => 'Good service',
        ]);
        $review->id = 1;

        // Expect both methods to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateServiceRatings')
            ->with($service->id);

        // Trigger the created event
        $this->observer->created($review);
    }

    // ─────────────────────────────────────────────────────────────
    // Updated Event Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that updated event triggers rating update
     * 
     * @validates Requirements 2.4
     */
    public function test_updated_event_triggers_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 3,
            'comment' => 'Average',
        ]);

        // Update the review
        $review->rating = 5;
        $review->save();

        // Refresh to get updated values
        $review->refresh();

        // Expect updateConsultantRatings to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        // Trigger the updated event
        $this->observer->updated($review);
    }

    /**
     * Test that updated event handles consultant_id change
     * 
     * @validates Requirements 2.4
     */
    public function test_updated_event_handles_consultant_id_change(): void
    {
        // Create test data
        $oldConsultant = Consultant::factory()->create();
        $newConsultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $oldConsultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $oldConsultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $oldConsultant->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Store the original consultant_id before changing
        $review->syncOriginal();
        
        // Now change the consultant_id
        $review->consultant_id = $newConsultant->id;
        
        // Manually set the changes array to simulate what Laravel does after save
        $review->syncChanges();

        // Track calls to verify both consultants are updated
        $calledWith = [];
        $this->ratingsCalculator
            ->expects($this->exactly(2))
            ->method('updateConsultantRatings')
            ->willReturnCallback(function ($consultantId) use (&$calledWith) {
                $calledWith[] = $consultantId;
            });

        // Trigger the updated event
        $this->observer->updated($review);

        // Verify both old and new consultant were updated
        $this->assertContains($oldConsultant->id, $calledWith);
        $this->assertContains($newConsultant->id, $calledWith);
    }

    /**
     * Test that updated event handles consultant_service_id change
     * 
     * @validates Requirements 2.4
     */
    public function test_updated_event_handles_service_id_change(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $oldService = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $newService = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $oldService->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $oldService->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Store the original service_id before changing
        $review->syncOriginal();
        
        // Now change the consultant_service_id
        $review->consultant_service_id = $newService->id;
        
        // Manually set the changes array to simulate what Laravel does after save
        $review->syncChanges();

        // Track calls to verify both services are updated
        $serviceCalledWith = [];
        $this->ratingsCalculator
            ->expects($this->exactly(2))
            ->method('updateServiceRatings')
            ->willReturnCallback(function ($serviceId) use (&$serviceCalledWith) {
                $serviceCalledWith[] = $serviceId;
            });

        // Expect updateConsultantRatings to be called once for the consultant
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        // Trigger the updated event
        $this->observer->updated($review);

        // Verify both old and new service were updated
        $this->assertContains($oldService->id, $serviceCalledWith);
        $this->assertContains($newService->id, $serviceCalledWith);
    }

    // ─────────────────────────────────────────────────────────────
    // Deleted Event Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that deleted event triggers rating update
     * 
     * @validates Requirements 2.5
     */
    public function test_deleted_event_triggers_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);

        // Expect updateConsultantRatings to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        // Trigger the deleted event
        $this->observer->deleted($review);
    }

    /**
     * Test that deleted event triggers rating update for service
     * 
     * @validates Requirements 2.5
     */
    public function test_deleted_event_triggers_service_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Expect both methods to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateServiceRatings')
            ->with($service->id);

        // Trigger the deleted event
        $this->observer->deleted($review);
    }

    // ─────────────────────────────────────────────────────────────
    // Restored Event Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that restored event triggers rating update
     * 
     * @validates Requirements 2.6
     */
    public function test_restored_event_triggers_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);

        // Expect updateConsultantRatings to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        // Trigger the restored event
        $this->observer->restored($review);
    }

    /**
     * Test that restored event triggers rating update for service
     * 
     * @validates Requirements 2.6
     */
    public function test_restored_event_triggers_service_rating_update(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $service = ConsultantService::factory()->create(['consultant_id' => $consultant->id]);
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'consultant_service_id' => $service->id,
            'client_id' => $client->id,
            'rating' => 4,
        ]);

        // Expect both methods to be called
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->with($consultant->id);

        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateServiceRatings')
            ->with($service->id);

        // Trigger the restored event
        $this->observer->restored($review);
    }

    // ─────────────────────────────────────────────────────────────
    // Error Handling Tests
    // ─────────────────────────────────────────────────────────────

    /**
     * Test that created event logs and re-throws exceptions
     * 
     * @validates Requirements 2.3
     */
    public function test_created_event_logs_and_rethrows_exceptions(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = new Review([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);
        $review->id = 1;

        // Make the service throw an exception
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->willThrowException(new \Exception('Database error'));

        // Mock Log facade
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($review, $consultant) {
                return $message === 'Failed to update ratings after review creation'
                    && $context['review_id'] === $review->id
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Database error'
                    && isset($context['trace']);
            });

        // Expect exception to be re-thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        // Trigger the created event
        $this->observer->created($review);
    }

    /**
     * Test that updated event logs and re-throws exceptions
     * 
     * @validates Requirements 2.4
     */
    public function test_updated_event_logs_and_rethrows_exceptions(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 3,
        ]);

        // Update the review
        $review->rating = 5;
        $review->save();
        $review->refresh();

        // Make the service throw an exception
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->willThrowException(new \Exception('Update failed'));

        // Mock Log facade
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($review, $consultant) {
                return $message === 'Failed to update ratings after review update'
                    && $context['review_id'] === $review->id
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Update failed'
                    && isset($context['trace'])
                    && isset($context['changed_attributes']);
            });

        // Expect exception to be re-thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Update failed');

        // Trigger the updated event
        $this->observer->updated($review);
    }

    /**
     * Test that deleted event logs and re-throws exceptions
     * 
     * @validates Requirements 2.5
     */
    public function test_deleted_event_logs_and_rethrows_exceptions(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);

        // Make the service throw an exception
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->willThrowException(new \Exception('Delete failed'));

        // Mock Log facade
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($review, $consultant) {
                return $message === 'Failed to update ratings after review deletion'
                    && $context['review_id'] === $review->id
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Delete failed'
                    && isset($context['trace']);
            });

        // Expect exception to be re-thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Delete failed');

        // Trigger the deleted event
        $this->observer->deleted($review);
    }

    /**
     * Test that restored event logs and re-throws exceptions
     * 
     * @validates Requirements 2.6
     */
    public function test_restored_event_logs_and_rethrows_exceptions(): void
    {
        // Create test data
        $consultant = Consultant::factory()->create();
        $client = User::factory()->create();
        $booking = Booking::factory()->completed()->create([
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'consultant_id' => $consultant->id,
            'client_id' => $client->id,
            'rating' => 5,
        ]);

        // Make the service throw an exception
        $this->ratingsCalculator
            ->expects($this->once())
            ->method('updateConsultantRatings')
            ->willThrowException(new \Exception('Restore failed'));

        // Mock Log facade
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($review, $consultant) {
                return $message === 'Failed to update ratings after review restoration'
                    && $context['review_id'] === $review->id
                    && $context['consultant_id'] === $consultant->id
                    && $context['error'] === 'Restore failed'
                    && isset($context['trace']);
            });

        // Expect exception to be re-thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Restore failed');

        // Trigger the restored event
        $this->observer->restored($review);
    }
}
