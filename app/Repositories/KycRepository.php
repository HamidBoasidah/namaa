<?php

namespace App\Repositories;

use App\Models\Kyc;
use App\Repositories\Eloquent\BaseRepository;

class KycRepository extends BaseRepository
{
    protected array $defaultWith = [
        // use relation:columns string so Eloquent selects only needed columns
        'user:id,first_name,last_name,email,phone_number,avatar',
    ];

    public function __construct(Kyc $model)
    {
        parent::__construct($model);
    }
}
