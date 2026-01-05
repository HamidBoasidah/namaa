<?php

namespace App\Services;

use App\Models\ConsultantWorkingHour;
use App\Repositories\ConsultantWorkingHourRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsultantWorkingHourService
{
    protected ConsultantWorkingHourRepository $workingHours;

    public function __construct(ConsultantWorkingHourRepository $workingHours)
    {
        $this->workingHours = $workingHours;
    }

    public function allForConsultant(int $consultantId)
    {
        return $this->workingHours->allForConsultant($consultantId, []);
    }

    public function activeForConsultant(int $consultantId)
    {
        return $this->workingHours->activeForConsultant($consultantId, []);
    }

    public function groupedByDay(int $consultantId, bool $onlyActive = true): Collection
    {
        return $this->workingHours->groupedByDay($consultantId, $onlyActive);
    }

    public function create(array $attributes): ConsultantWorkingHour
    {
        $this->assertNoOverlap(
            (int) $attributes['consultant_id'],
            (int) $attributes['day_of_week'],
            (string) $attributes['start_time'],
            (string) $attributes['end_time'],
            null
        );

        /** @var ConsultantWorkingHour $created */
        $created = $this->workingHours->create($attributes);
        return $created;
    }

    public function update(int $id, array $attributes): ConsultantWorkingHour
    {
        /** @var ConsultantWorkingHour $current */
        $current = $this->workingHours->findOrFail($id, []);

        $consultantId = (int) ($attributes['consultant_id'] ?? $current->consultant_id);
        $dayOfWeek    = (int) ($attributes['day_of_week'] ?? $current->day_of_week);
        $startTime    = (string) ($attributes['start_time'] ?? $current->start_time);
        $endTime      = (string) ($attributes['end_time'] ?? $current->end_time);

        $this->assertNoOverlap($consultantId, $dayOfWeek, $startTime, $endTime, $current->id);

        /** @var ConsultantWorkingHour $updated */
        $updated = $this->workingHours->update($id, $attributes);
        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->workingHours->delete($id);
    }

    public function activate(int $id): ConsultantWorkingHour
    {
        /** @var ConsultantWorkingHour $record */
        $record = $this->workingHours->findOrFail($id, []);

        // ✅ حتى عند التفعيل: نفحص التداخل ضد الكل (مفعّل وغير مفعّل)
        $this->assertNoOverlap(
            (int) $record->consultant_id,
            (int) $record->day_of_week,
            (string) $record->start_time,
            (string) $record->end_time,
            $record->id
        );

        $record->update(['is_active' => true]);
        return $record;
    }

    public function deactivate(int $id): ConsultantWorkingHour
    {
        /** @var ConsultantWorkingHour $record */
        $record = $this->workingHours->findOrFail($id, []);
        $record->update(['is_active' => false]);
        return $record;
    }

    /**
     * استبدال جدول الأسبوع بالكامل (Bulk Replace)
     *
     * weekPayload مثال:
     * [
     *   0 => [ ['start_time'=>'09:00','end_time'=>'12:00','is_active'=>true], ... ],
     *   1 => [ ... ],
     * ]
     */
    public function replaceWeeklySchedule(int $consultantId, array $weekPayload): void
    {
        DB::transaction(function () use ($consultantId, $weekPayload) {
            $this->workingHours->deleteForConsultant($consultantId);

            foreach ($weekPayload as $day => $intervals) {
                $day = (int) $day;
                if (!is_array($intervals)) {
                    continue;
                }

                $normalized = collect($intervals)
                    ->filter(fn ($row) => is_array($row))
                    ->map(function ($row) {
                        return [
                            'start_time' => (string) ($row['start_time'] ?? ''),
                            'end_time'   => (string) ($row['end_time'] ?? ''),
                            'is_active'  => (bool) ($row['is_active'] ?? true),
                        ];
                    })
                    ->sortBy('start_time')
                    ->values();

                // ✅ فحص التداخل داخل اليوم يشمل الكل (حتى غير المفعّل)
                $this->assertNoOverlapsWithinDay($normalized);

                foreach ($normalized as $row) {
                    $this->workingHours->create([
                        'consultant_id' => $consultantId,
                        'day_of_week'   => $day,
                        'start_time'    => $row['start_time'],
                        'end_time'      => $row['end_time'],
                        'is_active'     => $row['is_active'],
                    ]);
                }
            }
        });
    }

    /**
     * ✅ يمنع التداخل ضد كل السجلات (مفعّل وغير مفعّل)
     */
    protected function assertNoOverlap(
        int $consultantId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): void {
        $has = $this->workingHours->hasOverlap(
            $consultantId,
            $dayOfWeek,
            $startTime,
            $endTime,
            $ignoreId
        );

        if ($has) {
            throw ValidationException::withMessages([
                'end_time' => ['يوجد تداخل مع فترة عمل أخرى في نفس اليوم لهذا المستشار.'],
            ]);
        }
    }

    /**
     * ✅ فحص تداخل داخل نفس اليوم لمجموعة فترات (يشمل الكل)
     */
    protected function assertNoOverlapsWithinDay(Collection $intervals): void
    {
        $items = $intervals->values();

        // تحقق من صحة كل فترة
        foreach ($items as $row) {
            if (empty($row['start_time']) || empty($row['end_time'])) {
                throw ValidationException::withMessages([
                    'weekly_schedule' => ['وقت البداية ووقت النهاية مطلوبان لكل فترة.'],
                ]);
            }

            if ($row['start_time'] >= $row['end_time']) {
                throw ValidationException::withMessages([
                    'weekly_schedule' => ['وقت النهاية يجب أن يكون بعد وقت البداية.'],
                ]);
            }
        }

        // بعد الفرز: كل فترة يجب أن تنتهي قبل بداية التي بعدها
        for ($i = 0; $i < $items->count() - 1; $i++) {
            $current = $items[$i];
            $next    = $items[$i + 1];

            if ($current['end_time'] > $next['start_time']) {
                throw ValidationException::withMessages([
                    'weekly_schedule' => ['يوجد تداخل بين فترات العمل داخل نفس اليوم.'],
                ]);
            }
        }
    }
}