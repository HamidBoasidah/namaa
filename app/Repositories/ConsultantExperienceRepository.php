<?php

namespace App\Repositories;

use App\Models\ConsultantExperience;
use App\Repositories\Eloquent\BaseRepository;

class ConsultantExperienceRepository extends BaseRepository
{
    protected array $defaultWith = [];

    public function __construct(ConsultantExperience $model)
    {
        parent::__construct($model);
    }
}
