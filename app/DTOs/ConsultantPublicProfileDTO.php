<?php

namespace App\DTOs;

use App\Models\Consultant;
use App\Models\ConsultantService;
use Illuminate\Support\Facades\Storage;

class ConsultantPublicProfileDTO extends BaseDTO
{
    public int $consultant_id;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $email;
    public ?string $phone_number;
    public ?string $avatar;
    public array $certificates;
    public array $experiences;
    public array $services;
    public ?string $price_per_hour;
    public ?float $rating_avg;
    public int $ratings_count;
    public bool $is_favorite;

    public function __construct(
        int $consultant_id,
        ?string $first_name,
        ?string $last_name,
        ?string $email,
        ?string $phone_number,
        ?string $avatar,
        array $certificates,
        array $experiences,
        array $services,
        ?string $price_per_hour,
        ?float $rating_avg,
        int $ratings_count,
        bool $is_favorite = false
    ) {
        $this->consultant_id = $consultant_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->avatar = $avatar;
        $this->certificates = $certificates;
        $this->experiences = $experiences;
        $this->services = $services;
        $this->price_per_hour = $price_per_hour;
        $this->rating_avg = $rating_avg;
        $this->ratings_count = $ratings_count;
        $this->is_favorite = $is_favorite;
    }

    public static function fromModel(Consultant $consultant, bool $isFavorite = false): self
    {
        // جلب معلومات المستخدم
        $user = $consultant->user;
        
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
                    'rating_avg' => $svc->rating_avg !== null ? (float) $svc->rating_avg : 0.0,
                    'ratings_count' => (int) ($svc->ratings_count ?? 0),
                ];
            })->values()->toArray();

        // السعر بالساعة ومتوسط التقييم من نموذج المستشار
        $pricePerHour = $consultant->price_per_hour ? (string) $consultant->price_per_hour : '0.00';
        $ratingAvg = $consultant->rating_avg ? (float) $consultant->rating_avg : 0.0;
        $ratingsCount = (int) ($consultant->ratings_count ?? 0);

        return new self(
            $consultant->id,
            $user->first_name ?? null,
            $user->last_name ?? null,
            $user->email ?? null,
            $user->phone_number ?? null,
            $user->avatar ? Storage::url($user->avatar) : null,
            $certificates,
            $experiences,
            $services
            , $pricePerHour
            , $ratingAvg
            , $ratingsCount
            , $isFavorite
        );
    }

    public function toArray(): array
    {
        return [
            'consultant_id' => $this->consultant_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'certificates' => $this->certificates,
            'experiences' => $this->experiences,
            'services' => $this->services,
            'price_per_hour' => $this->price_per_hour,
            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'is_favorite' => $this->is_favorite,
        ];
    }
}
