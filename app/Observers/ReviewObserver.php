<?php

namespace App\Observers;

use App\Models\Review;
use App\Services\RatingsCalculatorService;
use Illuminate\Support\Facades\Log;

class ReviewObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private RatingsCalculatorService $ratingsCalculator
    ) {}

    /**
     * Handle the Review "created" event
     * 
     * When a review is created, update the ratings for the consultant
     * and the service (if applicable).
     * 
     * @param Review $review
     * @return void
     */
    public function created(Review $review): void
    {
        try {
            $this->updateRatings($review);
        } catch (\Exception $e) {
            Log::error('Failed to update ratings after review creation', [
                'review_id' => $review->id,
                'consultant_id' => $review->consultant_id,
                'consultant_service_id' => $review->consultant_service_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to ensure the issue is visible
            throw $e;
        }
    }

    /**
     * Handle the Review "updated" event
     * 
     * When a review is updated, update the ratings for the consultant
     * and the service (if applicable). If the consultant_id or 
     * consultant_service_id changed, also update the old consultant/service.
     * 
     * @param Review $review
     * @return void
     */
    public function updated(Review $review): void
    {
        try {
            // If consultant_id changed, update the old consultant's ratings
            if ($review->wasChanged('consultant_id')) {
                $oldConsultantId = $review->getOriginal('consultant_id');
                if ($oldConsultantId) {
                    $this->ratingsCalculator->updateConsultantRatings($oldConsultantId);
                }
            }

            // If consultant_service_id changed, update the old service's ratings
            if ($review->wasChanged('consultant_service_id')) {
                $oldServiceId = $review->getOriginal('consultant_service_id');
                if ($oldServiceId) {
                    $this->ratingsCalculator->updateServiceRatings($oldServiceId);
                }
            }

            // Update the current consultant and service ratings
            $this->updateRatings($review);
        } catch (\Exception $e) {
            Log::error('Failed to update ratings after review update', [
                'review_id' => $review->id,
                'consultant_id' => $review->consultant_id,
                'consultant_service_id' => $review->consultant_service_id,
                'changed_attributes' => $review->getChanges(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to ensure the issue is visible
            throw $e;
        }
    }

    /**
     * Handle the Review "deleted" event
     * 
     * When a review is deleted (soft delete), update the ratings
     * for the consultant and the service (if applicable).
     * 
     * @param Review $review
     * @return void
     */
    public function deleted(Review $review): void
    {
        try {
            $this->updateRatings($review);
        } catch (\Exception $e) {
            Log::error('Failed to update ratings after review deletion', [
                'review_id' => $review->id,
                'consultant_id' => $review->consultant_id,
                'consultant_service_id' => $review->consultant_service_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to ensure the issue is visible
            throw $e;
        }
    }

    /**
     * Handle the Review "restored" event
     * 
     * When a review is restored from soft delete, update the ratings
     * for the consultant and the service (if applicable).
     * 
     * @param Review $review
     * @return void
     */
    public function restored(Review $review): void
    {
        try {
            $this->updateRatings($review);
        } catch (\Exception $e) {
            Log::error('Failed to update ratings after review restoration', [
                'review_id' => $review->id,
                'consultant_id' => $review->consultant_id,
                'consultant_service_id' => $review->consultant_service_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to ensure the issue is visible
            throw $e;
        }
    }

    /**
     * Update ratings for consultant and service (if applicable)
     * 
     * This private method is called by all event handlers to update
     * the ratings for the consultant and optionally the service.
     * 
     * @param Review $review
     * @return void
     */
    private function updateRatings(Review $review): void
    {
        // Always update consultant ratings
        $this->ratingsCalculator->updateConsultantRatings($review->consultant_id);

        // Update service ratings if review is for a specific service
        if ($review->consultant_service_id) {
            $this->ratingsCalculator->updateServiceRatings($review->consultant_service_id);
        }
    }
}
