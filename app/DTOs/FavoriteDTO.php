<?php

namespace App\DTOs;

use App\Models\Favorite;
use App\DTOs\ConsultantMobileDTO;

class FavoriteDTO extends BaseDTO
{
    public int $id;
    public array $consultant;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(int $id, array $consultant, ?string $created_at, ?string $updated_at)
    {
        $this->id = $id;
        $this->consultant = $consultant;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public static function fromModel(Favorite $favorite): self
    {
        $consultantUser = $favorite->consultant?->user;
        $name = trim(($consultantUser?->first_name ?? '') . ' ' . ($consultantUser?->last_name ?? ''));

        // Use ConsultantMobileDTO to represent consultant payload for consistency with mobile API
        $consultantDto = null;
        if ($favorite->consultant) {
            $consultantDto = ConsultantMobileDTO::fromModel($favorite->consultant)->toArray();
        }

        return new self(
            id: $favorite->id,
            consultant: $consultantDto ?? [],
            created_at: $favorite->created_at?->format('Y-m-d\TH:i:s'),
            updated_at: $favorite->updated_at?->format('Y-m-d\TH:i:s')
        );
    }

    public function toArray(): array
    {
        $consultant = $this->consultant;

        // If consultant was provided as a model instance, convert using ConsultantMobileDTO
        if (is_object($consultant) && $consultant instanceof \App\Models\Consultant) {
            $consultant = ConsultantMobileDTO::fromModel($consultant)->toArray();
        }

        if (!is_array($consultant)) {
            $consultant = [];
        }

        return [
            'id' => $this->id,
            'consultant' => $consultant,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
