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
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id,
            'display_name' => $this->faker->name(),
            'bio' => $this->faker->paragraph(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('5########'),
            'years_of_experience' => $this->faker->numberBetween(0, 40),
            'specialization_summary' => $this->faker->sentence(),
            'profile_image' => null,
            'address' => $this->faker->address(),
            'governorate_id' => \App\Models\Governorate::inRandomOrder()->first()?->id,
            'district_id' => \App\Models\District::inRandomOrder()->first()?->id,
            'area_id' => \App\Models\Area::inRandomOrder()->first()?->id,
            'rating_avg' => $this->faker->randomFloat(2, 1, 5),
            'ratings_count' => $this->faker->numberBetween(0, 500),
            'is_active' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
