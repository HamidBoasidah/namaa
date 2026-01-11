<?php

namespace App\DTOs;

use App\Models\Category;

class CategoryMobileDTO extends BaseDTO
{
    public int $id;
    public string $name;
    public ?string $icon_url;
    public ?int $consultants_count;

    public function __construct(
        int $id,
        string $name,
        ?string $icon_url,
        ?int $consultants_count = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->icon_url = $icon_url;
        $this->consultants_count = $consultants_count;
    }

    public static function fromModel(Category $category): self
    {
        return new self(
            $category->id,
            $category->name ?? '',
            $category->icon_url ?? null,
            $category->consultants_count ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
        ];
    }

    public function toArrayWithCount(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'consultants_count' => $this->consultants_count ?? 0,
        ];
    }
}
