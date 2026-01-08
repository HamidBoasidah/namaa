<?php

namespace App\Services;

use App\Models\ConsultantHoliday;
use App\Repositories\ConsultantHolidayRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsultantHolidayService
{
    protected ConsultantHolidayRepository $holidays;

    public function __construct(ConsultantHolidayRepository $holidays)
    {
        $this->holidays = $holidays;
    }

    public function allForConsultant(int $consultantId)
    {
        return $this->holidays->allForConsultant($consultantId, []);
    }

    public function replaceHolidays(int $consultantId, array $holidayPayload): void
    {
        DB::transaction(function () use ($consultantId, $holidayPayload) {
            $normalized = collect($holidayPayload)
                ->filter(fn ($row) => is_array($row))
                ->map(function ($row) {
                    return [
                        'holiday_date' => (string) ($row['holiday_date'] ?? ''),
                        'name'         => $row['name'] ?? null,
                    ];
                })
                ->filter(fn ($row) => !empty($row['holiday_date']))
                ->map(function ($row) {
                    // enforce date format
                    $date = $row['holiday_date'];
                    if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date)) {
                        throw ValidationException::withMessages([
                            'holiday_date' => ['صيغة التاريخ يجب أن تكون YYYY-MM-DD.'],
                        ]);
                    }
                    $parsed = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
                    if ($parsed->lt(Carbon::today())) {
                        throw ValidationException::withMessages([
                            'holiday_date' => ['تاريخ الإجازة يجب أن يكون اليوم أو تاريخًا مستقبليًا.'],
                        ]);
                    }

                    $row['name'] = $row['name'] ? (string) $row['name'] : null;
                    return $row;
                })
                ->sortBy('holiday_date')
                ->values();

            $this->assertNoDuplicateDates($normalized);

            $this->holidays->deleteForConsultant($consultantId);

            foreach ($normalized as $row) {
                $this->holidays->create([
                    'consultant_id' => $consultantId,
                    'holiday_date'  => $row['holiday_date'],
                    'name'          => $row['name'],
                ]);
            }
        });
    }

    protected function assertNoDuplicateDates(Collection $items): void
    {
        $dates = $items->pluck('holiday_date');
        if ($dates->count() !== $dates->unique()->count()) {
            throw ValidationException::withMessages([
                'holiday_date' => ['لا يمكن تكرار نفس تاريخ الإجازة.'],
            ]);
        }
    }
}
