<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if user can view the review
     * Reviews are publicly viewable
     */
    public function view(?User $user, Review $review): bool
    {
        return true;
    }

    /**
     * Determine if user can create a review for a booking
     * - User must be the client who owns the booking
     * - Booking must be completed
     */
    public function create(User $user, Booking $booking): bool
    {
        return (int)$booking->client_id === (int)$user->id
            && $booking->status === Booking::STATUS_COMPLETED;
    }

    /**
     * Determine if user can update the review
     * - User must be the client who created the review
     */
    public function update(User $user, Review $review): bool
    {
        return (int)$review->client_id === (int)$user->id;
    }

    /**
     * Determine if user can delete the review
     * - User must be the client who created the review
     */
    public function delete(User $user, Review $review): bool
    {
        return (int)$review->client_id === (int)$user->id;
    }
}
