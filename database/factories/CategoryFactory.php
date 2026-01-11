<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'consultation_type_id' => \App\Models\ConsultationType::inRandomOrder()->first()?->id ?? \App\Models\ConsultationType::factory()->create()->id,
            'name' => $this->faker->unique()->word,
            'slug' => $this->faker->unique()->slug,
            'is_active' => $this->faker->boolean(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
