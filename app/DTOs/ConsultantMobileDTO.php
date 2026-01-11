<?php

namespace App\DTOs;

use App\Models\Consultant;

class ConsultantMobileDTO extends BaseDTO
{
    public int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $avatar;
    public float $rating_avg;
    public int $ratings_count;
    public array $service_categories;

    public function __construct(
        int $id,
        ?string $first_name,
        ?string $last_name,
        ?string $avatar,
        float $rating_avg,
        int $ratings_count,
        array $service_categories = []
    ) {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->avatar = $avatar;
        $this->rating_avg = $rating_avg;
        $this->ratings_count = $ratings_count;
        $this->service_categories = $service_categories;
    }

    public static function fromModel(Consultant $consultant, array $serviceCategories = []): self
    {
        return new self(
            $consultant->id,
            $consultant->user?->first_name,
            $consultant->user?->last_name,
            $consultant->user?->avatar ? asset('storage/' . $consultant->user->avatar) : null,
            (float) ($consultant->rating_avg ?? 0),
            (int) ($consultant->ratings_count ?? 0),
            $serviceCategories
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'avatar' => $this->avatar,
            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'service_categories' => $this->service_categories,
        ];
    }
}
