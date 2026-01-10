<?php

namespace App\DTOs;

use App\Models\ConsultantExperience;

class ConsultantExperienceDTO extends BaseDTO
{
    public $id;
    public $consultant_id;
    public $name;
    public $is_active;
    public $created_at;

    public function __construct($id, $consultant_id, $name, $is_active, $created_at)
    {
        $this->id = $id;
        $this->consultant_id = $consultant_id;
        $this->name = $name;
        $this->is_active = $is_active;
        $this->created_at = $created_at;
    }

    public static function fromModel(ConsultantExperience $experience): self
    {
        return new self(
            $experience->id,
            $experience->consultant_id,
            $experience->name,
            $experience->is_active,
            $experience->created_at?->toDateTimeString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'consultant_id' => $this->consultant_id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
