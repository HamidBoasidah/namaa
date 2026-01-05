<?php

namespace App\Repositories;

use App\Models\ConsultantWorkingHour;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ConsultantWorkingHourRepository extends BaseRepository
{
    protected array $defaultWith = [];

    public function __construct(ConsultantWorkingHour $model)
    {
        parent::__construct($model);
    }

    public function forConsultant(int $consultantId, ?array $with = null): Builder
    {
        return $this->query($with)->where('consultant_id', $consultantId);
    }

    public function allForConsultant(int $consultantId, ?array $with = null)
    {
        return $this->forConsultant($consultantId, $with)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function activeForConsultant(int $consultantId, ?array $with = null)
    {
        return $this->forConsultant($consultantId, $with)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function forConsultantDay(int $consultantId, int $dayOfWeek, bool $onlyActive = false, ?array $with = null)
    {
        $q = $this->forConsultant($consultantId, $with)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time');

        if ($onlyActive) {
            $q->where('is_active', true);
        }

        return $q->get();
    }

    /**
     * ✅ فحص التداخل يشمل كل الفترات (مفعلة وغير مفعلة)
     * overlap if:
     * new_start < existing_end AND new_end > existing_start
     */
    public function hasOverlap(
        int $consultantId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreId = null
    ): bool {
        return $this->query([])
            ->where('consultant_id', $consultantId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function deleteForConsultant(int $consultantId): int
    {
        return $this->query([])
            ->where('consultant_id', $consultantId)
            ->delete();
    }

    public function deleteForConsultantDay(int $consultantId, int $dayOfWeek): int
    {
        return $this->query([])
            ->where('consultant_id', $consultantId)
            ->where('day_of_week', $dayOfWeek)
            ->delete();
    }

    public function setDayActive(int $consultantId, int $dayOfWeek, bool $active): int
    {
        return $this->query([])
            ->where('consultant_id', $consultantId)
            ->where('day_of_week', $dayOfWeek)
            ->update(['is_active' => $active]);
    }

    public function groupedByDay(int $consultantId, bool $onlyActive = true): Collection
    {
        $items = $onlyActive
            ? $this->activeForConsultant($consultantId, [])
            : $this->allForConsultant($consultantId, []);

        return $items->groupBy('day_of_week');
    }
}