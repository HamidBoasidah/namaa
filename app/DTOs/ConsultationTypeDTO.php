<?php

namespace App\DTOs;

use App\Models\ConsultationType;

class ConsultationTypeDTO extends BaseDTO
{
    public $id;
    public $name;
    public $slug;
    public $is_active;
    public $created_at;
    public $deleted_at;

    public function __construct($id, $name, $slug, $is_active, $created_at = null, $deleted_at = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->is_active = (bool) $is_active;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(ConsultationType $item): self
    {
        return new self(
            $item->id,
            $item->name ?? null,
            $item->slug ?? null,
            $item->is_active ?? false,
            $item->created_at?->toDateTimeString() ?? null,
            $item->deleted_at?->toDateTimeString() ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
        ];
    }
}
