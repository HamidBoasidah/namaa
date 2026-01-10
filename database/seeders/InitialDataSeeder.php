<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Tag;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\ConsultantExperience;


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

        // Create some consultants
        Consultant::factory()->count(10)->create();

        // Create consultant services and certificates for each consultant
        $consultants = Consultant::all();
        foreach ($consultants as $consultant) {
            // Create Certificate for each consultant
            Certificate::factory()->create([
                'consultant_id' => $consultant->id,
            ]);

            ConsultantService::factory()->count(rand(1, 3))->create([
                'consultant_id' => $consultant->id,
            ]);
            // create 1-4 experiences per consultant
            ConsultantExperience::factory()->count(rand(1,4))->create([
                'consultant_id' => $consultant->id,
            ]);
        }
    }
}
