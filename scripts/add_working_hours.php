<?php

$consultants = \App\Models\Consultant::all();
$created = 0;

foreach ($consultants as $consultant) {
    // Add Friday (5) and Saturday (6)
    for ($day = 5; $day <= 6; $day++) {
        $exists = \App\Models\ConsultantWorkingHour::where('consultant_id', $consultant->id)
            ->where('day_of_week', $day)
            ->exists();
        
        if (!$exists) {
            \App\Models\ConsultantWorkingHour::create([
                'consultant_id' => $consultant->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
            ]);
            $created++;
            echo "Created day $day for consultant {$consultant->id}\n";
        }
    }
}

echo "Created $created additional working hour records\n";
