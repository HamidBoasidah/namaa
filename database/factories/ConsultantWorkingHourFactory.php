<?php

namespace Database\Factories;

use App\Models\ConsultantWorkingHour;
use App\Models\Consultant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultantWorkingHourFactory extends Factory
{
    protected $model = ConsultantWorkingHour::class;

    public function definition()
    {
        // pick a random consultant if not provided
        $consultantId = Consultant::inRandomOrder()->value('id') ?? Consultant::factory()->create()->id;

        // random day between 0 (Sunday) and 6 (Saturday)
        $day = $this->faker->numberBetween(0, 6);

        // standard working window: pick hour between 7 and 14 for start, duration 4-8 hours
        $startHour = $this->faker->numberBetween(7, 14);
        $duration = $this->faker->numberBetween(4, 8);

        $start = sprintf('%02d:00', $startHour);
        $end = sprintf('%02d:00', min(23, $startHour + $duration));

        return [
            'consultant_id' => $consultantId,
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a full day (09:00-17:00)
     */
    public function fullDay()
    {
        return $this->state(function (array $attributes) {
            return [
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
            ];
        });
    }
}
