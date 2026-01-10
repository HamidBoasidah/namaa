<?php

namespace App\DTOs;

use App\Models\Area;

class AreaDTO extends BaseDTO
{
    public $id;
    public $name_ar;
    public $name_en;
    public $is_active;
    public $district_id;
    public $district_name_ar;
    public $district_name_en;
    

    public function __construct($id, $name_ar, $name_en, $is_active, $district_id, $district_name_ar = null, $district_name_en = null)
    {
        $this->id = $id;
        $this->name_ar = $name_ar;
        $this->name_en = $name_en;
        $this->is_active = $is_active;
        $this->district_id = $district_id;
        $this->district_name_ar = $district_name_ar;
        $this->district_name_en = $district_name_en;
    }

    public static function fromModel(Area $area): self
    {
        $d_ar = $area->district ? $area->district->name_ar : null;
        $d_en = $area->district ? $area->district->name_en : null;

        return new self(
            $area->id,
            $area->name_ar,
            $area->name_en,
            $area->is_active,
            $area->district_id,
            $d_ar,
            $d_en
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'is_active' => $this->is_active,
            'district_id' => $this->district_id,
            'district_name_ar' => $this->district_name_ar,
            'district_name_en' => $this->district_name_en,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'is_active' => $this->is_active,
            'district_id' => $this->district_id,
            'district_name_ar' => $this->district_name_ar,
            'district_name_en' => $this->district_name_en,
        ];
    }
}
