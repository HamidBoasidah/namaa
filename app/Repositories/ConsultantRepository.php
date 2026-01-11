<?php

namespace App\Repositories;

use App\Models\Consultant;
use App\Repositories\Eloquent\BaseRepository;

class ConsultantRepository extends BaseRepository
{
    protected array $defaultWith = [
        // ✅ ساعات العمل الفعّالة مرتبة
        //'activeWorkingHours:id,consultant_id,day_of_week,start_time,end_time,is_active',

    ];

    public function __construct(Consultant $model)
    {
        parent::__construct($model);
    }
}