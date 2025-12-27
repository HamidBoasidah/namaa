<?php

namespace App\Repositories;

use App\Models\Governorate;
use App\Repositories\Eloquent\BaseRepository;

class GovernorateRepository extends BaseRepository
{
    public function __construct(Governorate $model)
    {
        parent::__construct($model);
    }

}
