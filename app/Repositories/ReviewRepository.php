<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository extends BaseRepository
{
    /**
     * Default eager-loaded relationships for reviews
     */
    protected array $defaultWith = [
        'client:id,first_name,last_name,avatar',
        'consultant.user:id,first_name,last_name,avatar',
        'booking:id,start_at,end_at,status',
    ];

    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a review by its booking ID
     */
    public function findByBookingId(int $bookingId): ?Review
    {
        return $this->makeQuery()
            ->where('booking_id', $bookingId)
            ->first();
    }

    /**
     * Check if a booking already has a review (including soft-deleted)
     * This enforces the one-review-per-booking constraint
     */
    public function bookingHasReview(int $bookingId): bool
    {
        return $this->model->newQuery()
            ->withTrashed()
            ->where('booking_id', $bookingId)
            ->exists();
    }

    /**
     * Get paginated reviews for a consultant
     */
    public function forConsultant(int $consultantId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->makeQuery()
            ->where('consultant_id', $consultantId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated reviews for a client
     */
    public function forClient(int $clientId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->makeQuery()
            ->where('client_id', $clientId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated reviews for a specific consultant service
     */
    public function forConsultantService(int $consultantServiceId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->makeQuery()
            ->whereHas('booking', function ($query) use ($consultantServiceId) {
                $query->where('bookable_type', 'App\\Models\\ConsultantService')
                      ->where('bookable_id', $consultantServiceId);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all reviews ordered by rating (highest first)
     */
    public function allOrderedByRating(int $perPage = 10): LengthAwarePaginator
    {
        return $this->makeQuery()
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
