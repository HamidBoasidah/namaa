<?php

namespace Database\Factories;

use App\Models\Consultant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultantFactory extends Factory
{
    protected $model = Consultant::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::where('user_type', 'consultant')->inRandomOrder()->value('id')
                ?? \App\Models\User::factory()->create(['user_type' => 'consultant'])->id,
            'consultation_type_id' => \App\Models\ConsultationType::inRandomOrder()->first()?->id ?? \App\Models\ConsultationType::factory()->create()->id,
            'years_of_experience' => $this->faker->numberBetween(0, 40),
            'rating_avg' => $this->faker->randomFloat(2, 0, 5),
            'ratings_count' => $this->faker->numberBetween(0, 500),
            'price_per_hour' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => $this->faker->boolean(80),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
