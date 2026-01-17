<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        // Prefer an existing completed booking; otherwise create one
        $booking = Booking::where('status', Booking::STATUS_COMPLETED)->inRandomOrder()->first()
            ?? Booking::factory()->completed()->create();

        return [
            'booking_id' => $booking->id,
            'consultant_id' => $booking->consultant_id,
            'client_id' => $booking->client_id,
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.7)->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Associate the review with a specific booking
     */
    public function forBooking(Booking $booking): static
    {
        return $this->state(function (array $attributes) use ($booking) {
            return [
                'booking_id' => $booking->id,
                'consultant_id' => $booking->consultant_id,
                'client_id' => $booking->client_id,
            ];
        });
    }

    /**
     * Positive review (4-5)
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Negative review (1-2)
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(1, 2),
        ]);
    }
}
