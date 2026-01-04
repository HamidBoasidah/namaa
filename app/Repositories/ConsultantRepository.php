<?php

namespace App\Repositories;

use App\Models\Consultant;
use App\Repositories\Eloquent\BaseRepository;

class ConsultantRepository extends BaseRepository
{
    protected array $defaultWith = [
        'governorate:id,name_ar,name_en',
        'district:id,name_ar,name_en',
        'area:id,name_ar,name_en',
    ];

    public function __construct(Consultant $model)
    {
        parent::__construct($model);
    }
}