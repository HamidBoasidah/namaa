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
    public $icon_path;
    public $icon_url;
    public $price;
    public $buffer;
    public $duration_minutes;
    public $consultation_method;
    public $delivery_time;
    public $auto_accept_requests;
    public $is_active;

    public $tags;
    
    // تفاصيل الخدمة
    public $includes;
    public $target_audience;
    public $deliverables;

    public $created_at;
    public $deleted_at;

    // Store model for consultant info
    protected ?ConsultantService $model = null;

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
        $icon_path,
        $icon_url,
        $price,
        $buffer,
        $duration_minutes,
        $consultation_method,
        $delivery_time,
        $auto_accept_requests,
        $is_active,
        $tags = [],
        $includes = [],
        $target_audience = [],
        $deliverables = [],
        $created_at = null,
        $deleted_at = null,
        ?ConsultantService $model = null
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
        $this->icon_path = $icon_path;
        $this->icon_url = $icon_url;
        $this->price = $price;
        $this->buffer = $buffer;
        $this->duration_minutes = $duration_minutes;
        $this->consultation_method = $consultation_method;
        $this->delivery_time = $delivery_time;
        $this->auto_accept_requests = $auto_accept_requests;
        $this->is_active = $is_active;
        $this->tags = $tags ?? [];
        $this->includes = $includes ?? [];
        $this->target_audience = $target_audience ?? [];
        $this->deliverables = $deliverables ?? [];
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
        $this->model = $model;
    }

    public static function fromModel(ConsultantService $service): self
    {
        $user = $service->consultant?->user;
        $displayName = $user ? trim("{$user->first_name} {$user->last_name}") : null;
        
        return new self(
            $service->id,
            $service->consultant_id,
            $displayName,
            $user?->email,
            $user?->phone_number,
            $service->category_id,
            $service->category?->name,
            $service->title,
            $service->description,
            $service->icon_path,
            $service->icon_url,
            (string) ($service->price ?? '0.00'),
            (int) ($service->buffer ?? 0),
            (int) ($service->duration_minutes ?? 60),
            $service->consultation_method ?? 'video',
            $service->delivery_time,
            (bool) ($service->auto_accept_requests ?? false),
            (bool) ($service->is_active ?? false),
            $service->tags?->toArray() ?? [],
            $service->includes?->pluck('content')->toArray() ?? [],
            $service->targetAudience?->pluck('content')->toArray() ?? [],
            $service->deliverables?->pluck('content')->toArray() ?? [],
            $service->created_at?->toDateTimeString(),
            $service->deleted_at?->toDateTimeString(),
            $service
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
            'icon_path' => $this->icon_path,
            'icon_url' => $this->icon_url,
            'price' => $this->price,
            'buffer' => $this->buffer,
            'duration_minutes' => $this->duration_minutes,
            'consultation_method' => $this->consultation_method,
            'delivery_time' => $this->delivery_time,
            'auto_accept_requests' => $this->auto_accept_requests,
            'is_active' => $this->is_active,
            'tags' => $this->tags,
            'includes' => $this->includes,
            'target_audience' => $this->target_audience,
            'deliverables' => $this->deliverables,
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
            'category_name' => $this->category_name,
            'title' => $this->title,
            'icon_url' => $this->icon_url,
            'price' => $this->price,
            'buffer' => $this->buffer,
            'duration_minutes' => $this->duration_minutes,
            'consultation_method' => $this->consultation_method,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * For public list API - minimal fields
     */
    public function toListArray(): array
    {
        return [
            'id' => $this->id,
            'icon_url' => $this->icon_url,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * For public detail API - with consultant info
     */
    public function toDetailArray(): array
    {
        $consultant = $this->model?->consultant;
        $user = $consultant?->user;
        
        $experiences = $consultant?->experiences?->where('is_active', true)->map(function ($exp) {
            return [
                'name' => $exp->name,
                'organization' => $exp->organization ?? null,
                'years' => $exp->years ?? null,
            ];
        })->values()->toArray() ?? [];

        return [
            'id' => $this->id,
            'icon_url' => $this->icon_url,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'consultation_method' => $this->consultation_method,
            'delivery_time' => $this->delivery_time,
            'includes' => $this->includes,
            'target_audience' => $this->target_audience,
            'deliverables' => $this->deliverables,
            'consultant' => $consultant ? [
                'avatar' => $user?->avatar ? asset('storage/' . $user->avatar) : null,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'consultation_type_name' => $consultant->consultationType?->name,
                'experiences' => $experiences,
            ] : null,
        ];
    }
}
