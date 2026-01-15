<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consultant;
use App\Models\ConsultantWorkingHour;

class ConsultantWorkingHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates default working hours for all consultants if they don't already have any.
     * By default it creates full-day hours (09:00-17:00) for weekdays (0-6) or just for weekends depending on preference.
     * Here we will create 5 working days (Sunday-Thursday, days 0-4) as full days if a consultant has no working hours.
     *
     * Run: php artisan db:seed --class=ConsultantWorkingHourSeeder
     */
    public function run()
    {
        Consultant::chunk(100, function ($consultants) {
            foreach ($consultants as $consultant) {
                $existing = ConsultantWorkingHour::where('consultant_id', $consultant->id)->exists();
                if ($existing) {
                    continue;
                }

                // create full day hours for Sunday-Thursday (0-4)
                for ($day = 0; $day <= 4; $day++) {
                    ConsultantWorkingHour::factory()
                        ->state(['consultant_id' => $consultant->id, 'day_of_week' => $day])
                        ->fullDay()
                        ->create();
                }

                // optionally create weekend shorter hours (Friday=5, Saturday=6)
                for ($day = 5; $day <= 6; $day++) {
                    ConsultantWorkingHour::factory()->state([
                        'consultant_id' => $consultant->id,
                        'day_of_week' => $day,
                        'start_time' => '09:00',
                        'end_time' => '13:00',
                        'is_active' => true,
                    ])->create();
                }
            }
        });
    }
}
