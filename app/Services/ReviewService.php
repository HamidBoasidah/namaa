<?php

namespace App\Services;

use App\DTOs\CreateReviewDTO;
use App\DTOs\UpdateReviewDTO;
use App\Models\Booking;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(
        protected ReviewRepository $reviews
    ) {}

    /**
     * Create a new review
     * 
     * Validates:
     * - Booking exists and belongs to the authenticated client
     * - Booking status is 'completed'
     * - No existing review for this booking (including soft-deleted)
     * 
     * Derives consultant_id and client_id from the booking
     * 
     * @throws ValidationException
     */
    public function createReview(CreateReviewDTO $dto): Review
    {
        /** @var Booking $booking */
        $booking = Booking::query()->find($dto->booking_id);

        // Validate booking exists
        if (!$booking) {
            throw ValidationException::withMessages([
                'booking_id' => ['الحجز غير موجود'],
            ]);
        }

        // Validate booking belongs to client (ownership check)
        if ((int)$booking->client_id !== (int)$dto->client_id) {
            throw ValidationException::withMessages([
                'booking_id' => ['لا يمكنك تقييم حجز لا يخصك'],
            ]);
        }

        // Validate booking status is 'completed'
        if ($booking->status !== Booking::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'booking_id' => ['لا يمكن تقييم حجز غير مكتمل'],
            ]);
        }

        // Check no existing review (including soft-deleted)
        if ($this->reviews->bookingHasReview($booking->id)) {
            throw ValidationException::withMessages([
                'booking_id' => ['تم تقييم هذا الحجز مسبقاً'],
            ]);
        }

        // Derive consultant_id and client_id from booking
        $data = [
            'booking_id' => $booking->id,
            'consultant_id' => $booking->consultant_id,
            'client_id' => $booking->client_id,
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ];

        return $this->reviews->create($data);
    }

    /**
     * Update an existing review
     * 
     * Validates:
     * - Review exists
     * - Review belongs to the authenticated client
     * 
     * @throws ValidationException
     */
    public function updateReview(int $reviewId, UpdateReviewDTO $dto, int $clientId): Review
    {
        $review = $this->reviews->find($reviewId);

        // Validate review exists
        if (!$review) {
            throw ValidationException::withMessages([
                'review' => ['التقييم غير موجود'],
            ]);
        }

        // Validate review belongs to client (ownership check)
        if ((int)$review->client_id !== (int)$clientId) {
            throw ValidationException::withMessages([
                'review' => ['لا يمكنك تعديل تقييم لا يخصك'],
            ]);
        }

        // Update review
        $review->update([
            'rating' => $dto->rating,
            'comment' => $dto->comment,
        ]);

        return $review->refresh();
    }

    /**
     * Soft delete an existing review
     * 
     * Validates:
     * - Review exists
     * - Review belongs to the authenticated client
     * 
     * @throws ValidationException
     */
    public function deleteReview(int $reviewId, int $clientId): bool
    {
        $review = $this->reviews->find($reviewId);

        // Validate review exists
        if (!$review) {
            throw ValidationException::withMessages([
                'review' => ['التقييم غير موجود'],
            ]);
        }

        // Validate review belongs to client (ownership check)
        if ((int)$review->client_id !== (int)$clientId) {
            throw ValidationException::withMessages([
                'review' => ['لا يمكنك حذف تقييم لا يخصك'],
            ]);
        }

        // Soft delete
        return (bool) $review->delete();
    }

    /**
     * Find a review by ID
     */
    public function find(int $id): ?Review
    {
        return $this->reviews->find($id);
    }

    /**
     * Get paginated reviews for a consultant
     */
    public function getConsultantReviews(int $consultantId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviews->forConsultant($consultantId, $perPage);
    }

    /**
     * Get paginated reviews for the authenticated client
     */
    public function getMyReviews(int $clientId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviews->forClient($clientId, $perPage);
    }

    /**
     * Get paginated reviews for a specific consultant service
     */
    public function getConsultantServiceReviews(int $consultantServiceId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviews->forConsultantService($consultantServiceId, $perPage);
    }

    /**
     * Get all reviews ordered by rating (highest first)
     */
    public function getAllReviewsByRating(int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviews->allOrderedByRating($perPage);
    }
}
