<?php

namespace App\Repositories;

use App\Models\ConsultantService;
use App\Repositories\Eloquent\BaseRepository;

class ConsultantServiceRepository extends BaseRepository
{
    protected array $defaultWith = [
        'consultant:id,user_id',
        'consultant.user:id,first_name,last_name,email,phone_number',
        'category:id,name',
        'tags:id,name',
        'includes',
        'targetAudience',
        'deliverables',
    ];

    public function __construct(ConsultantService $model)
    {
        parent::__construct($model);
    }
}
