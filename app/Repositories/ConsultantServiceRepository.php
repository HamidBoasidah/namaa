<?php

namespace App\Repositories;

use App\Models\ConsultantService;
use App\Repositories\Eloquent\BaseRepository;

class ConsultantServiceRepository extends BaseRepository
{
    protected array $defaultWith = [
        'consultant:id,user_id,display_name,email,phone',
        'tags:id,name',
    ];

    public function __construct(ConsultantService $model)
    {
        parent::__construct($model);
    }
}