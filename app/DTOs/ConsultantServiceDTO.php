<?php

namespace App\DTOs;

use App\Models\ConsultantService;

class ConsultantServiceDTO extends BaseDTO
{
    public $id;

    public $consultant_id;
    public $consultant_display_name;
    public $consultant_email;
    public $consultant_phone;
    public $category_id;
    public $category_name;

    public $title;
    public $description;
    public $price;
    public $duration_minutes;
    public $is_active;

    public $tags;

    public $created_at;
    public $deleted_at;

    public function __construct(
        $id,

        $consultant_id,
        $consultant_display_name,
        $consultant_email,
        $consultant_phone,
        $category_id,
        $category_name,

        $title,
        $description,
        $price,
        $duration_minutes,
        $is_active,
        $tags = [],

        $created_at = null,
        $deleted_at = null
    ) {
        $this->id = $id;

        $this->consultant_id = $consultant_id;
        $this->consultant_display_name = $consultant_display_name;
        $this->consultant_email = $consultant_email;
        $this->consultant_phone = $consultant_phone;
        $this->category_id = $category_id;
        $this->category_name = $category_name;

        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->duration_minutes = $duration_minutes;
        $this->is_active = $is_active;

        $this->tags = $tags ?? [];

        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(ConsultantService $service): self
    {
        return new self(
            $service->id,

            $service->consultant_id,
            $service->consultant?->display_name,
            $service->consultant?->email,
            $service->consultant?->phone,
            $service->category_id,
            $service->category?->name,

            $service->title,
            $service->description,
            (string) ($service->price ?? '0.00'),
            (int) ($service->duration_minutes ?? 60),
            (bool) ($service->is_active ?? false),

            $service->tags?->toArray(),

            $service->created_at?->toDateTimeString(),
            $service->deleted_at?->toDateTimeString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,

            'consultant_id' => $this->consultant_id,
            'consultant_display_name' => $this->consultant_display_name,
            'consultant_email' => $this->consultant_email,
            'consultant_phone' => $this->consultant_phone,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,

            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'is_active' => $this->is_active,

            'tags' => $this->tags,

            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,

            'consultant_id' => $this->consultant_id,
            'consultant_display_name' => $this->consultant_display_name,
            'consultant_email' => $this->consultant_email,
            'consultant_phone' => $this->consultant_phone,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,

            'title' => $this->title,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'is_active' => $this->is_active,
        ];
    }
}
