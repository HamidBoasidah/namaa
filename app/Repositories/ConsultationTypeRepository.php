<?php

namespace App\Repositories;

use App\Models\ConsultationType;
use App\Repositories\Eloquent\BaseRepository;

class ConsultationTypeRepository extends BaseRepository
{
    protected array $defaultWith = [];

    public function __construct(ConsultationType $model)
    {
        parent::__construct($model);
    }
}
