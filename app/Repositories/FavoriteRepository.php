<?php

namespace App\Repositories;

use App\Models\Favorite;
use App\Repositories\Eloquent\BaseRepository;

class FavoriteRepository extends BaseRepository
{
    protected array $defaultWith = [
        'consultant.user:id,first_name,last_name,avatar,email',
    ];

    public function __construct(Favorite $model)
    {
        parent::__construct($model);
    }

    public function existsForUser(int $userId, int $consultantId): bool
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('consultant_id', $consultantId)
            ->exists();
    }
}