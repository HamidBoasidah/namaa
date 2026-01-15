<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Consultant;
use App\Models\ConsultantService;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing consultants and customers only (not consultants as clients)
        $consultants = Consultant::all();
        $customers = User::where('user_type', 'customer')->get();
        
        // Fallback: users who are not consultants
        if ($customers->isEmpty()) {
            $consultantUserIds = Consultant::pluck('user_id')->toArray();
            $customers = User::whereNotIn('id', $consultantUserIds)->get();
        }

        if ($consultants->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('يجب وجود مستشارين وعملاء في قاعدة البيانات أولاً');
            return;
        }

        $this->command->info('جاري إنشاء حجوزات وهمية...');
        $this->command->info('عدد العملاء المتاحين: ' . $customers->count());

        // Create confirmed bookings (most common)
        Booking::factory()
            ->count(20)
            ->confirmed()
            ->inFuture()
            ->create();

        $this->command->info('✓ تم إنشاء 20 حجز مؤكد');

        // Create pending bookings
        Booking::factory()
            ->count(5)
            ->pending()
            ->inFuture()
            ->create();

        $this->command->info('✓ تم إنشاء 5 حجوزات معلقة');

        // Create completed bookings (in the past)
        Booking::factory()
            ->count(15)
            ->completed()
            ->create();

        $this->command->info('✓ تم إنشاء 15 حجز مكتمل');

        // Create cancelled bookings
        Booking::factory()
            ->count(5)
            ->cancelled()
            ->create();

        $this->command->info('✓ تم إنشاء 5 حجوزات ملغاة');

        // Create expired bookings
        Booking::factory()
            ->count(3)
            ->expired()
            ->create();

        $this->command->info('✓ تم إنشاء 3 حجوزات منتهية الصلاحية');

        // Create service bookings if services exist
        $services = ConsultantService::all();
        if ($services->isNotEmpty()) {
            foreach ($services->take(5) as $service) {
                Booking::factory()
                    ->forService($service)
                    ->confirmed()
                    ->inFuture()
                    ->create();
            }
            $this->command->info('✓ تم إنشاء 5 حجوزات لخدمات');
        }

        $this->command->info('');
        $this->command->info('تم إنشاء ' . Booking::count() . ' حجز بنجاح!');
    }
}
