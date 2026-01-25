<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateReviewDTO;
use App\DTOs\ReviewDTO;
use App\DTOs\UpdateReviewDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Traits\ExceptionHandler;
use App\Http\Traits\SuccessResponse;
use App\Models\Booking;
use App\Repositories\ReviewRepository;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use ExceptionHandler, SuccessResponse;

    public function __construct(
        protected ReviewService $reviewService,
        protected ReviewRepository $reviewRepository
    ) {
        $this->middleware('auth:sanctum')->except(['index']);
    }

        /**
     * Get all reviews ordered by rating (highest first)
     * GET /api/reviews
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        $reviews = $this->reviewService->getAllReviewsByRating($perPage);

        // Transform to DTOs
        $reviews = $reviews->through(function ($review) {
            return ReviewDTO::fromModel($review)->toArray();
        });

        // prepare rating counts (1..5)
        $countsRaw = DB::table('reviews')
            ->select('rating', DB::raw('count(*) as cnt'))
            ->groupBy('rating')
            ->pluck('cnt', 'rating')
            ->toArray();

        $counts = [];
        for ($r = 5; $r >= 1; $r--) {
            $counts[(string)$r] = isset($countsRaw[$r]) ? (int) $countsRaw[$r] : 0;
        }

        $response = [
            'success' => true,
            'message' => 'تم جلب قائمة التقييمات بنجاح',
            'status_code' => 200,
            'data' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
            'counts' => $counts,
        ];

        return response()->json($response, 200);
    }
    
    /**
     * Create a new review
     * POST /api/reviews
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $dto = CreateReviewDTO::fromRequest(
            $request->validated(),
            $request->user()->id
        );

        $review = $this->reviewService->createReview($dto);

        return $this->createdResponse(
            ReviewDTO::fromModel($review->fresh(['client', 'consultant.user', 'booking']))->toArray(),
            'تم إنشاء التقييم بنجاح'
        );
    }

    /**
     * Show a single review
     * GET /api/reviews/{id}
     */
    public function show(int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            $this->throwNotFoundException('التقييم غير موجود');
        }

        $this->authorize('view', $review);

        return $this->resourceResponse(
            ReviewDTO::fromModel($review)->toArray(),
            'تم جلب بيانات التقييم بنجاح'
        );
    }

    /**
     * Update an existing review
     * PUT /api/reviews/{id}
     */
    public function update(UpdateReviewRequest $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            $this->throwNotFoundException('التقييم غير موجود');
        }

        $this->authorize('update', $review);

        $dto = UpdateReviewDTO::fromRequest($request->validated());

        $updatedReview = $this->reviewService->updateReview($id, $dto, $request->user()->id);

        return $this->updatedResponse(
            ReviewDTO::fromModel($updatedReview->fresh(['client', 'consultant.user', 'booking']))->toArray(),
            'تم تحديث التقييم بنجاح'
        );
    }

    /**
     * Soft delete a review
     * DELETE /api/reviews/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        if (!$review) {
            $this->throwNotFoundException('التقييم غير موجود');
        }

        $this->authorize('delete', $review);

        $this->reviewService->deleteReview($id, $request->user()->id);

        return $this->deletedResponse('تم حذف التقييم بنجاح');
    }

    /**
     * Get authenticated user's reviews
     * GET /api/my/reviews
     */
    public function myReviews(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);

        $reviews = $this->reviewService->getMyReviews($request->user()->id, $perPage);

        // Transform to DTOs
        $reviews = $reviews->through(function ($review) {
            return ReviewDTO::fromModel($review)->toArray();
        });

        return $this->collectionResponse($reviews, 'تم جلب قائمة التقييمات بنجاح');
    }

    /**
     * Get review by booking id (if exists)
     * GET /api/bookings/{id}/review
     */
    public function reviewByBooking(int $bookingId): JsonResponse
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            $this->throwNotFoundException('الحجز غير موجود');
        }

        // Ensure user can view this booking
        $this->authorize('view', $booking);

        $review = $this->reviewService->findByBookingId($bookingId);

        if (!$review) {
            return $this->successResponse(null, 'لا يوجد تقييم لهذا الحجز', 200);
        }

        return $this->resourceResponse(
            ReviewDTO::fromModel($review)->toArray(),
            'تم جلب تقييم الحجز بنجاح'
        );
    }


}
