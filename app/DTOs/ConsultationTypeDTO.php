<?php

namespace App\DTOs;

use App\Models\ConsultationType;

class ConsultationTypeDTO extends BaseDTO
{
    public $id;
    public $name;
    public $slug;
    public $icon_path;
    public $icon_url;
    public $is_active;
    public $consultants_count;
    public $created_at;
    public $deleted_at;

    public function __construct($id, $name, $slug, $icon_path, $icon_url, $is_active, $consultants_count = 0, $created_at = null, $deleted_at = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->icon_path = $icon_path;
        $this->icon_url = $icon_url;
        $this->is_active = (bool) $is_active;
        $this->consultants_count = (int) $consultants_count;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(ConsultationType $item): self
    {
        return new self(
            $item->id,
            $item->name ?? null,
            $item->slug ?? null,
            $item->icon_path ?? null,
            $item->icon_url ?? null,
            $item->is_active ?? false,
            $item->consultants_count ?? 0,
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
            'icon_path' => $this->icon_path,
            'icon_url' => $this->icon_url,
            'is_active' => $this->is_active,
            'consultants_count' => $this->consultants_count,
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
            'icon_url' => $this->icon_url,
            'is_active' => $this->is_active,
            'consultants_count' => $this->consultants_count,
        ];
    }
}
