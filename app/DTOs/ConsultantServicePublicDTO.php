<?php

namespace App\DTOs;

use App\Models\ConsultantService;

/**
 * DTO للعرض العام لخدمات المستشار
 * يستخدم في API الجوال لعرض قائمة الخدمات وتفاصيلها
 */
class ConsultantServicePublicDTO extends BaseDTO
{
    public int $id;
    public ?string $icon_url;
    public string $title;
    public ?string $description;
    public ?string $price;
    public ?int $duration_minutes;
    public ?string $consultation_method;
    public ?string $delivery_time;
    public array $includes;
    public array $target_audience;
    public array $deliverables;
    public ?array $consultant;

    public function __construct(
        int $id,
        ?string $icon_url,
        string $title,
        ?string $description,
        ?string $price,
        ?int $duration_minutes,
        ?string $consultation_method,
        ?string $delivery_time,
        array $includes = [],
        array $target_audience = [],
        array $deliverables = [],
        ?array $consultant = null
    ) {
        $this->id = $id;
        $this->icon_url = $icon_url;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->duration_minutes = $duration_minutes;
        $this->consultation_method = $consultation_method;
        $this->delivery_time = $delivery_time;
        $this->includes = $includes;
        $this->target_audience = $target_audience;
        $this->deliverables = $deliverables;
        $this->consultant = $consultant;
    }

    /**
     * إنشاء DTO من النموذج مع تحميل العلاقات المطلوبة
     * يحمل المستشار مع user, consultationType, و experiences
     * 
     * @param ConsultantService $service
     * @return self
     */
    public static function fromModel(ConsultantService $service): self
    {
        // تحميل العلاقات المطلوبة إذا لم تكن محملة
        if (!$service->relationLoaded('consultant')) {
            $service->load(['consultant.user', 'consultant.consultationType', 'consultant.experiences']);
        } elseif ($service->consultant && !$service->consultant->relationLoaded('user')) {
            $service->consultant->load(['user', 'consultationType', 'experiences']);
        }

        // تحميل تفاصيل الخدمة إذا لم تكن محملة
        if (!$service->relationLoaded('includes')) {
            $service->load(['includes', 'targetAudience', 'deliverables']);
        }

        // بناء معلومات المستشار
        $consultantData = null;
        if ($service->consultant) {
            $consultant = $service->consultant;
            $user = $consultant->user;
            
            // بناء مصفوفة الخبرات
            $experiences = [];
            if ($consultant->relationLoaded('experiences') && $consultant->experiences) {
                foreach ($consultant->experiences as $experience) {
                    $experiences[] = [
                        'name' => $experience->name,
                        'organization' => $experience->organization ?? null,
                        'years' => $experience->years ?? null,
                    ];
                }
            }

            $consultantData = [
                'avatar' => $user?->avatar,
                'first_name' => $user?->first_name,
                'last_name' => $user?->last_name,
                'consultation_type_name' => $consultant->consultationType?->name,
                'experiences' => $experiences,
            ];
        }

        return new self(
            $service->id,
            $service->icon_url,
            $service->title,
            $service->description,
            $service->price ? (string) $service->price : null,
            $service->duration_minutes,
            $service->consultation_method,
            $service->delivery_time,
            $service->includes?->pluck('content')->toArray() ?? [],
            $service->targetAudience?->pluck('content')->toArray() ?? [],
            $service->deliverables?->pluck('content')->toArray() ?? [],
            $consultantData
        );
    }

    /**
     * تحويل إلى مصفوفة للقائمة
     * يُرجع فقط: id, icon_url, title, description
     * 
     * @return array
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
     * تحويل إلى مصفوفة للتفاصيل
     * يُرجع كل المعلومات بما في ذلك معلومات المستشار
     * 
     * @return array
     */
    public function toDetailArray(): array
    {
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
            'consultant' => $this->consultant,
        ];
    }
}
