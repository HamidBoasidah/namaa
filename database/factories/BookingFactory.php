<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $durations = [30, 45, 60, 90, 120];
        $buffers = [0, 5, 10, 15];
        
        $consultant = Consultant::inRandomOrder()->first();
        // اختيار عميل فقط (ليس مستشار) كصاحب الحجز
        $client = User::where('user_type', 'customer')->inRandomOrder()->first()
            ?? User::inRandomOrder()->first();
        
        // Random start time in the next 30 days, aligned to 5-minute intervals
        $startAt = Carbon::now()
            ->addDays($this->faker->numberBetween(1, 30))
            ->setHour($this->faker->numberBetween(9, 16))
            ->setMinute($this->faker->randomElement([0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55]))
            ->setSecond(0);
        
        $durationMinutes = $this->faker->randomElement($durations);
        $endAt = $startAt->copy()->addMinutes($durationMinutes);
        
        // Randomly choose bookable type
        $isServiceBooking = $this->faker->boolean(50);
        
        if ($isServiceBooking && $consultant) {
            $service = ConsultantService::where('consultant_id', $consultant->id)->inRandomOrder()->first();
            if ($service) {
                $bookableType = ConsultantService::class;
                $bookableId = $service->id;
                $durationMinutes = $service->duration_minutes;
                $endAt = $startAt->copy()->addMinutes($durationMinutes);
            } else {
                $bookableType = Consultant::class;
                $bookableId = $consultant?->id;
            }
        } else {
            $bookableType = Consultant::class;
            $bookableId = $consultant?->id;
        }

        return [
            'client_id' => $client?->id,
            'consultant_id' => $consultant?->id,
            'bookable_type' => $bookableType,
            'bookable_id' => $bookableId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => $durationMinutes,
            'buffer_after_minutes' => $this->faker->randomElement($buffers),
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => null,
            'cancelled_at' => null,
            'cancel_reason' => null,
            'cancelled_by_type' => null,
            'cancelled_by_id' => null,
            'notes' => $this->faker->optional(0.3)->sentence(),
            // Snapshot price: service price or consultant hourly rate * duration
            'price' => $this->determinePrice($bookableType, $consultant ?? null, $service ?? null, $durationMinutes),
        ];
    }

    /**
     * Determine booking price for factory
     */
    protected function determinePrice($bookableType, ?Consultant $consultant, ?ConsultantService $service, int $durationMinutes): float
    {
        if ($bookableType === ConsultantService::class && $service) {
            return (float) ($service->price ?? 0);
        }

        if ($consultant) {
            $hourly = (float) ($consultant->price ?? 0);
            return round($hourly * ($durationMinutes / 60), 2);
        }

        return 0.00;
    }

    /**
     * Indicate that the booking is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_PENDING,
            'expires_at' => now()->addMinutes(Booking::PENDING_HOLD_MINUTES),
        ]);
    }

    /**
     * Indicate that the booking is confirmed
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled
     */
    public function cancelled(): static
    {
        $canceller = $this->faker->boolean(70) 
            ? User::inRandomOrder()->first() 
            : \App\Models\Admin::inRandomOrder()->first();

        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_CANCELLED,
            'expires_at' => null,
            'cancelled_at' => now()->subHours($this->faker->numberBetween(1, 48)),
            'cancel_reason' => $this->faker->optional(0.7)->randomElement([
                'تغيير في الجدول',
                'ظروف طارئة',
                'تم الحجز بالخطأ',
                'تأجيل الموعد',
                'أسباب شخصية',
            ]),
            'cancelled_by_type' => $canceller ? get_class($canceller) : null,
            'cancelled_by_id' => $canceller?->id,
        ]);
    }

    /**
     * Indicate that the booking is completed
     */
    public function completed(): static
    {
        $pastStart = Carbon::now()
            ->subDays($this->faker->numberBetween(1, 30))
            ->setHour($this->faker->numberBetween(9, 16))
            ->setMinute($this->faker->randomElement([0, 15, 30, 45]))
            ->setSecond(0);

        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_COMPLETED,
            'expires_at' => null,
            'start_at' => $pastStart,
            'end_at' => $pastStart->copy()->addMinutes($attributes['duration_minutes'] ?? 60),
        ]);
    }

    /**
     * Indicate that the booking is expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_EXPIRED,
            'expires_at' => now()->subMinutes($this->faker->numberBetween(1, 60)),
        ]);
    }

    /**
     * Set a specific consultant
     */
    public function forConsultant(Consultant $consultant): static
    {
        return $this->state(fn (array $attributes) => [
            'consultant_id' => $consultant->id,
            'bookable_type' => Consultant::class,
            'bookable_id' => $consultant->id,
        ]);
    }

    /**
     * Set a specific client
     */
    public function forClient(User $client): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    /**
     * Set a specific service
     */
    public function forService(ConsultantService $service): static
    {
        return $this->state(fn (array $attributes) => [
            'consultant_id' => $service->consultant_id,
            'bookable_type' => ConsultantService::class,
            'bookable_id' => $service->id,
            'duration_minutes' => $service->duration_minutes,
            'end_at' => Carbon::parse($attributes['start_at'])->addMinutes($service->duration_minutes),
            'buffer_after_minutes' => $service->buffer ?? 0,
        ]);
    }

    /**
     * Set a specific start time
     */
    public function startsAt(Carbon $startAt): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes($attributes['duration_minutes'] ?? 60),
        ]);
    }

    /**
     * Set booking in the past (for completed bookings)
     */
    public function inPast(): static
    {
        $pastStart = Carbon::now()
            ->subDays($this->faker->numberBetween(1, 60))
            ->setHour($this->faker->numberBetween(9, 16))
            ->setMinute($this->faker->randomElement([0, 15, 30, 45]))
            ->setSecond(0);

        return $this->state(fn (array $attributes) => [
            'start_at' => $pastStart,
            'end_at' => $pastStart->copy()->addMinutes($attributes['duration_minutes'] ?? 60),
        ]);
    }

    /**
     * Set booking in the future
     */
    public function inFuture(): static
    {
        $futureStart = Carbon::now()
            ->addDays($this->faker->numberBetween(1, 30))
            ->setHour($this->faker->numberBetween(9, 16))
            ->setMinute($this->faker->randomElement([0, 15, 30, 45]))
            ->setSecond(0);

        return $this->state(fn (array $attributes) => [
            'start_at' => $futureStart,
            'end_at' => $futureStart->copy()->addMinutes($attributes['duration_minutes'] ?? 60),
        ]);
    }
}
