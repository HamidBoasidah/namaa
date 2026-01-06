<?php

namespace Database\Factories;

use App\Models\ConsultantService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultantServiceFactory extends Factory
{
    protected $model = ConsultantService::class;

    public function definition()
    {
        $durations = [30, 45, 60, 90, 120];

        return [
            'consultant_id' => \App\Models\Consultant::inRandomOrder()->first()?->id,
            'category_id' => \App\Models\Category::inRandomOrder()->first()?->id,
            'title' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'duration_minutes' => $this->faker->randomElement($durations),
            'is_active' => $this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
