<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Kyc;
use App\Models\Tag;



class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing users
        $users = User::all();

        Tag::factory()->count(12)->create();

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
    }
}
