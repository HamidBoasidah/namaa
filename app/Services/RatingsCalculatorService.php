<?php

namespace App\Services;

use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RatingsCalculatorService
{
    /**
     * Update consultant ratings
     * 
     * Recalculates and updates the consultant's rating_avg and ratings_count
     * based on all non-deleted reviews for that consultant.
     * 
     * @param int $consultantId The consultant ID to update
     * @return void
     */
    public function updateConsultantRatings(int $consultantId): void
    {
        try {
            DB::transaction(function () use ($consultantId) {
                $ratings = $this->calculateRatings(
                    Review::where('consultant_id', $consultantId)
                );

                Consultant::where('id', $consultantId)->update([
                    'rating_avg' => $ratings['avg'],
                    'ratings_count' => $ratings['count'],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to update consultant ratings', [
                'consultant_id' => $consultantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Update consultant service ratings
     * 
     * Recalculates and updates the consultant service's rating_avg and ratings_count
     * based on all non-deleted reviews for that service.
     * 
     * @param int $serviceId The consultant service ID to update
     * @return void
     */
    public function updateServiceRatings(int $serviceId): void
    {
        try {
            DB::transaction(function () use ($serviceId) {
                $ratings = $this->calculateRatings(
                    Review::where('consultant_service_id', $serviceId)
                );

                ConsultantService::where('id', $serviceId)->update([
                    'rating_avg' => $ratings['avg'],
                    'ratings_count' => $ratings['count'],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to update service ratings', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Calculate ratings from a query builder
     * 
     * Uses aggregate queries (AVG, COUNT) to efficiently calculate
     * the average rating and count of reviews without loading all
     * records into memory. Only includes non-deleted reviews.
     * 
     * @param Builder $query Base query for reviews
     * @return array ['avg' => float, 'count' => int]
     */
    private function calculateRatings($query): array
    {
        $result = $query->selectRaw('
            COALESCE(AVG(rating), 0) as avg,
            COUNT(*) as count
        ')->first();

        return [
            'avg' => round($result->avg, 2),
            'count' => $result->count,
        ];
    }
}
