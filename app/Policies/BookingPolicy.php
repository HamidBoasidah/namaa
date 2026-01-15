<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Consultant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Get consultant for user (if user is a consultant)
     */
    protected function getConsultant(User $user): ?Consultant
    {
        return Consultant::where('user_id', $user->id)->first();
    }

    /**
     * Check if user is the client of the booking
     */
    protected function isClient(User $user, Booking $booking): bool
    {
        return $user->id === $booking->client_id;
    }

    /**
     * Check if user is the consultant of the booking
     */
    protected function isConsultant(User $user, Booking $booking): bool
    {
        $consultant = $this->getConsultant($user);
        return $consultant && $consultant->id === $booking->consultant_id;
    }

    /**
     * Determine if user can view any bookings (list)
     * All authenticated users can view their own bookings
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view the booking
     * - Client can view their own bookings
     * - Consultant can view bookings for their consultations
     * - Admin can view any booking
     */
    public function view(User $user, Booking $booking): bool
    {
        return $this->isClient($user, $booking) || $this->isConsultant($user, $booking);
    }

    /**
     * Admin can view any booking
     */
    public function viewAsAdmin(Admin $admin, Booking $booking): bool
    {
        return true;
    }

    /**
     * Determine if user can create bookings
     * All authenticated users can create bookings
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can confirm the booking
     * Only the client who created the booking can confirm it
     */
    public function confirm(User $user, Booking $booking): bool
    {
        return $this->isClient($user, $booking);
    }

    /**
     * Determine if user can cancel the booking
     * - Client can cancel their own bookings
     * - Consultant can cancel their consultation bookings
     * - Admin can cancel any booking
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $this->isClient($user, $booking) || $this->isConsultant($user, $booking);
    }

    /**
     * Admin can cancel any booking
     */
    public function cancelAsAdmin(Admin $admin, Booking $booking): bool
    {
        return true;
    }

    /**
     * Determine if user can update the booking
     * Currently not implemented - reserved for future rescheduling
     */
    public function update(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine if user can delete the booking
     * Soft delete only - same rules as cancel
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $this->cancel($user, $booking);
    }
}
