<?php

namespace App\DTOs;

use App\Models\Governorate;

class GovernorateDTO extends BaseDTO
{
    public $id;
    public $name_ar;
    public $name_en;
    public $is_active;
    

    public function __construct($id, $name_ar, $name_en, $is_active)
    {
        $this->id = $id;
        $this->name_ar = $name_ar;
        $this->name_en = $name_en;
        $this->is_active = $is_active;
    }

    public static function fromModel(Governorate $gov): self
    {
        return new self(
            $gov->id,
            $gov->name_ar,
            $gov->name_en,
            $gov->is_active
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'is_active' => $this->is_active,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'is_active' => $this->is_active,
        ];
    }
}
