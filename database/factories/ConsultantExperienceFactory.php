<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ConsultantExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // consultant_id should be supplied when creating via factory in seeder
            'name' => $this->faker->jobTitle(),
            'is_active' => $this->faker->boolean(90),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
