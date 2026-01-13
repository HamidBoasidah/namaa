<?php

namespace App\DTOs;

use App\Models\Consultant;
use Illuminate\Support\Facades\Storage;

class ConsultantPublicProfileDTO extends BaseDTO
{
    public int $consultant_id;
    public array $certificates;
    public array $experiences;
    public ?array $service;

    public function __construct(
        int $consultant_id,
        array $certificates,
        array $experiences,
        ?array $service
    ) {
        $this->consultant_id = $consultant_id;
        $this->certificates = $certificates;
        $this->experiences = $experiences;
        $this->service = $service;
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

        // تنسيق الخدمة
        $service = null;
        if ($consultant->service) {
            $service = [
                'id' => $consultant->service->id,
                'title' => $consultant->service->title,
                'description' => $consultant->service->description,
                'price' => (string) ($consultant->service->price ?? '0.00'),
                'category' => $consultant->service->category ? [
                    'id' => $consultant->service->category->id,
                    'name' => $consultant->service->category->name,
                ] : null,
            ];
        }

        return new self(
            $consultant->id,
            $certificates,
            $experiences,
            $service
        );
    }

    public function toArray(): array
    {
        return [
            'consultant_id' => $this->consultant_id,
            'certificates' => $this->certificates,
            'experiences' => $this->experiences,
            'service' => $this->service,
        ];
    }
}
