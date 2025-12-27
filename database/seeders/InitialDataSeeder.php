<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\WithdrawalMethod;
use App\Models\Chef;
use App\Models\ChefService;
use App\Models\ChefGallery;
use App\Models\ChefServiceImage;
use App\Models\ChefCategory;
use App\Models\ChefServiceTag;
use App\Models\Address;
use App\Models\Kyc;
use App\Models\Booking;
use App\Models\BookingTransaction;
use App\Models\ChefWallet;
use App\Models\ChefWalletTransaction;
use App\Models\ChefWithdrawalRequest;
use App\Models\ChefServiceRating;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Get existing users
        $users = User::all();

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
