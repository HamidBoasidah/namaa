<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ReviewDTO;
use App\Http\Controllers\Controller;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultantReviewsController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct(
        protected ReviewService $reviewService
    ) {
        // No auth middleware - public endpoint
    }

    /**
     * Get reviews for a consultant
     * GET /api/consultants/{id}/reviews
     */
    public function index(Request $request, int $consultantId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        $reviews = $this->reviewService->getConsultantReviews($consultantId, $perPage);

        // Transform to DTOs
        $reviews = $reviews->through(function ($review) {
            return ReviewDTO::fromModel($review)->toArray();
        });

        return $this->collectionResponse($reviews, 'تم جلب قائمة التقييمات بنجاح');
    }

    /**
     * Get reviews for a specific consultant service
     * GET /api/consultant-services/{id}/reviews
     */
    public function serviceReviews(Request $request, int $consultantServiceId): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        $reviews = $this->reviewService->getConsultantServiceReviews($consultantServiceId, $perPage);

        // Transform to DTOs
        $reviews = $reviews->through(function ($review) {
            return ReviewDTO::fromModel($review)->toArray();
        });

        return $this->collectionResponse($reviews, 'تم جلب قائمة التقييمات بنجاح');
    }
}
