<?php

namespace Database\Factories;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KycFactory extends Factory
{
    protected $model = Kyc::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'rejected_reason' => null,
            'is_verified' => $this->faker->boolean(30),
            'verified_at' => $this->faker->boolean(30) ? now() : null,
            'full_name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'date_of_birth' => $this->faker->date(),
            'address' => $this->faker->address(),
            'document_type' => $this->faker->randomElement(['passport', 'driving_license', 'id_card']),
            // store a placeholder filename (we don't write actual files during seeding)
            'document_scan_copy' => $this->faker->randomElement(['kycs']) . '/' . Str::uuid() . '.jpg',
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
