<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Kyc;
use App\Models\Tag;
use App\Models\Consultant;
use App\Models\ConsultantService;


class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing users
        $users = User::all();

        Tag::factory()->count(12)->create();
        Category::factory()->count(8)->create();
        // If no users exist, create some
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        // Create KYC for each user
        foreach ($users as $user) {
            Kyc::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        // Create some consultants
        Consultant::factory()->count(10)->create();

        // Create consultant services for each consultant (1-3 services each)
        $consultants = Consultant::all();
        foreach ($consultants as $consultant) {
            ConsultantService::factory()->count(rand(1, 3))->create([
                'consultant_id' => $consultant->id,
            ]);
        }
    }
}
