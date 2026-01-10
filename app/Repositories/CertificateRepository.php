<?php

namespace App\Repositories;

use App\Models\Certificate;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class CertificateRepository extends BaseRepository
{
    protected array $defaultWith = [
        'consultant.user:id,first_name,last_name,email,phone_number,avatar',
    ];

    public function __construct(Certificate $model)
    {
        parent::__construct($model);
    }

    /**
     * سجلات خاصة بمستشار معيّن
     */
    public function forConsultant(int $consultantId, ?array $with = null): Builder
    {
        return $this->makeQuery($with)->where('consultant_id', $consultantId);
    }

    /**
     * جميع السجلات الخاصة بمستشار معيّن
     */
    public function allForConsultant(int $consultantId, ?array $with = null)
    {
        return $this->forConsultant($consultantId, $with)
            ->latest()
            ->get();
    }

    /**
     * ترقيم (paginate) لسجلات مستشار معيّن
     */
    public function paginateForConsultant(int $consultantId, int $perPage = 10, ?array $with = null)
    {
        return $this->forConsultant($consultantId, $with)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * جلب سجل واحد يخص مستشار معيّن أو يرمي ModelNotFoundException
     */
    public function findForConsultant(int|string $id, int $consultantId, ?array $with = null)
    {
        return $this->forConsultant($consultantId, $with)->findOrFail($id);
    }
}
