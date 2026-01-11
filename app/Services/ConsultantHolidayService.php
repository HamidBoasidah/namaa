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

    /**
     * Get query builder for consultant's holidays
     */
    public function getQueryForConsultant(int $consultantId)
    {
        return $this->holidays->forConsultant($consultantId, []);
    }

    /**
     * Find holiday by ID
     */
    public function find(int $id): ConsultantHoliday
    {
        /** @var ConsultantHoliday $holiday */
        $holiday = $this->holidays->findOrFail($id, []);
        return $holiday;
    }

    /**
     * Find holiday for specific consultant
     */
    public function findForConsultant(int $id, int $consultantId): ConsultantHoliday
    {
        /** @var ConsultantHoliday $holiday */
        $holiday = $this->holidays->findOrFail($id, []);
        
        if ($holiday->consultant_id !== $consultantId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }
        
        return $holiday;
    }

    /**
     * Create a single holiday
     */
    public function create(array $attributes): ConsultantHoliday
    {
        $this->validateHolidayDate($attributes['holiday_date']);
        $this->assertNoDuplicateDate(
            (int) $attributes['consultant_id'],
            $attributes['holiday_date'],
            null
        );

        /** @var ConsultantHoliday $created */
        $created = $this->holidays->create($attributes);
        return $created;
    }

    /**
     * Update a holiday
     */
    public function update(int $id, array $attributes): ConsultantHoliday
    {
        /** @var ConsultantHoliday $current */
        $current = $this->holidays->findOrFail($id, []);

        if (isset($attributes['holiday_date'])) {
            $this->validateHolidayDate($attributes['holiday_date']);
            $this->assertNoDuplicateDate(
                (int) ($attributes['consultant_id'] ?? $current->consultant_id),
                $attributes['holiday_date'],
                $id
            );
        }

        /** @var ConsultantHoliday $updated */
        $updated = $this->holidays->update($id, $attributes);
        return $updated;
    }

    /**
     * Delete a holiday
     */
    public function delete(int $id): bool
    {
        return $this->holidays->delete($id);
    }

    /**
     * Validate holiday date format and ensure it's not in the past
     */
    protected function validateHolidayDate(string $date): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
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
    }

    /**
     * Assert no duplicate date for consultant
     */
    protected function assertNoDuplicateDate(int $consultantId, string $date, ?int $ignoreId = null): void
    {
        $query = $this->holidays->forConsultant($consultantId, [])
            ->where('holiday_date', $date);
        
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'holiday_date' => ['لا يمكن تكرار نفس تاريخ الإجازة.'],
            ]);
        }
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
