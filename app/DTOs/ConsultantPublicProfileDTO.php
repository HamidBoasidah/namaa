<?php

namespace App\DTOs;

use App\Models\Consultant;
use App\Models\ConsultantService;
use Illuminate\Support\Facades\Storage;

class ConsultantPublicProfileDTO extends BaseDTO
{
    public int $consultant_id;
    public array $certificates;
    public array $experiences;
    public array $services;

    public function __construct(
        int $consultant_id,
        array $certificates,
        array $experiences,
        array $services
    ) {
        $this->consultant_id = $consultant_id;
        $this->certificates = $certificates;
        $this->experiences = $experiences;
        $this->services = $services;
    }

    public static function fromModel(Consultant $consultant): self
    {
        // تنسيق الشهادات
        $certificates = $consultant->certificates->map(function ($cert) {
            return [
                'id' => $cert->id,
                // return the public URL to the stored document (or null)
                'document_scan_copy' => $cert->document_scan_copy ? Storage::url($cert->document_scan_copy) : null,
                'document_name' => $cert->document_scan_copy_original_name,
            ];
        })->toArray();

        // تنسيق الخبرات - الاسم فقط
        $experiences = $consultant->experiences
            ->where('is_active', true)
            ->map(function ($exp) {
                return [
                    'name' => $exp->name,
                ];
            })->values()->toArray();

        // تنسيق كل خدمات المستشار — نُعيد فقط الخدمات المفعلة
        $services = ConsultantService::where('consultant_id', $consultant->id)
            ->where('is_active', true)
            ->with('category')
            ->get()
            ->map(function ($svc) {
                return [
                    'id' => $svc->id,
                    'title' => $svc->title,
                    'description' => $svc->description,
                    'price' => (string) ($svc->price ?? '0.00'),
                    'category' => $svc->category ? [
                        'id' => $svc->category->id,
                        'name' => $svc->category->name,
                    ] : null,
                    'duration_minutes' => (int) ($svc->duration_minutes ?? 60),
                    'consultation_method' => $svc->consultation_method ?? 'video',
                ];
            })->values()->toArray();

        return new self(
            $consultant->id,
            $certificates,
            $experiences,
            $services
        );
    }

    public function toArray(): array
    {
        return [
            'consultant_id' => $this->consultant_id,
            'certificates' => $this->certificates,
            'experiences' => $this->experiences,
            'services' => $this->services,
        ];
    }
}
