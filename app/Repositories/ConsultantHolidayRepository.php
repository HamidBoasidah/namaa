<?php

namespace App\Repositories;

use App\Models\ConsultantHoliday;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ConsultantHolidayRepository extends BaseRepository
{
    protected array $defaultWith = [];

    public function __construct(ConsultantHoliday $model)
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
            ->orderBy('holiday_date')
            ->get();
    }

    public function deleteForConsultant(int $consultantId): int
    {
        return $this->query([])
            ->where('consultant_id', $consultantId)
            ->delete();
    }
}
