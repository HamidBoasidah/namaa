<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * For each completed booking that doesn't have a review, create a review with 70% probability.
     */
    public function run(): void
    {
        Booking::where('status', Booking::STATUS_COMPLETED)
            ->doesntHave('review')
            ->chunk(100, function ($bookings) {
                foreach ($bookings as $booking) {
                    // 70% chance to create a review for a completed booking
                    if (random_int(1, 100) <= 70) {
                        \Database\Factories\ReviewFactory::new()->forBooking($booking)->create();
                    }
                }
            });
    }
}
