<?php

namespace App\DTOs;

use App\Models\Consultant;

class ConsultantDTO extends BaseDTO
{
    public $id;
    public $user_id;
    public $user_name;
    public $user_email;
    public $user_phone;

    public $display_name;
    public $bio;
    public $email;
    public $phone;

    public $years_of_experience;
    public $specialization_summary;

    public $profile_image;
    public $address;

    // Location IDs
    public $governorate_id;
    public $district_id;
    public $area_id;

    // Location Names
    public $governorate_name_ar;
    public $governorate_name_en;

    public $district_name_ar;
    public $district_name_en;

    public $area_name_ar;
    public $area_name_en;

    // Ratings
    public $rating_avg;
    public $ratings_count;

    public $is_active;

    // ✅ Working hours
    public array $working_hours = [];
    public array $active_working_hours = [];

    // ✅ Holidays
    public array $holidays = [];

    public $created_at;
    public $deleted_at;

    public function __construct(
        $id,
        $user_id,
        $user_name,
        $user_email,
        $user_phone,

        $display_name,
        $bio,
        $email,
        $phone,

        $years_of_experience,
        $specialization_summary,

        $profile_image,
        $address,

        $governorate_id,
        $district_id,
        $area_id,

        $governorate_name_ar,
        $governorate_name_en,

        $district_name_ar,
        $district_name_en,

        $area_name_ar,
        $area_name_en,

        $rating_avg,
        $ratings_count,
        $is_active,

        array $working_hours = [],
        array $active_working_hours = [],

        array $holidays = [],

        $created_at = null,
        $deleted_at = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->user_email = $user_email;
        $this->user_phone = $user_phone;

        $this->display_name = $display_name;
        $this->bio = $bio;
        $this->email = $email;
        $this->phone = $phone;

        $this->years_of_experience = $years_of_experience;
        $this->specialization_summary = $specialization_summary;

        $this->profile_image = $profile_image;
        $this->address = $address;

        $this->governorate_id = $governorate_id;
        $this->district_id = $district_id;
        $this->area_id = $area_id;

        $this->governorate_name_ar = $governorate_name_ar;
        $this->governorate_name_en = $governorate_name_en;

        $this->district_name_ar = $district_name_ar;
        $this->district_name_en = $district_name_en;

        $this->area_name_ar = $area_name_ar;
        $this->area_name_en = $area_name_en;

        $this->rating_avg = $rating_avg;
        $this->ratings_count = $ratings_count;
        $this->is_active = $is_active;

        $this->working_hours = $working_hours;
        $this->active_working_hours = $active_working_hours;

        $this->holidays = $holidays;

        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(Consultant $consultant): self
    {
        return new self(
            $consultant->id,
            $consultant->user_id,
            $consultant->user?->name,
            $consultant->user?->email,
            $consultant->user?->phone_number,

            $consultant->display_name,
            $consultant->bio,
            $consultant->email,
            $consultant->phone,

            $consultant->years_of_experience,
            $consultant->specialization_summary,

            $consultant->profile_image,
            $consultant->address,

            $consultant->governorate_id,
            $consultant->district_id,
            $consultant->area_id,

            // Governorate names
            $consultant->governorate?->name_ar,
            $consultant->governorate?->name_en,

            // District names
            $consultant->district?->name_ar,
            $consultant->district?->name_en,

            // Area names
            $consultant->area?->name_ar,
            $consultant->area?->name_en,

            (float) ($consultant->rating_avg ?? 0),
            (int) ($consultant->ratings_count ?? 0),
            (bool) ($consultant->is_active ?? false),

            // ✅ Working hours (requires eager loading in controller)
            $consultant->workingHours?->map(fn ($wh) => [
                'id' => $wh->id,
                'day_of_week' => (int) $wh->day_of_week,
                'start_time' => (string) $wh->start_time,
                'end_time' => (string) $wh->end_time,
                'is_active' => (bool) $wh->is_active,
            ])->values()->all() ?? [],

            $consultant->activeWorkingHours?->map(fn ($wh) => [
                'id' => $wh->id,
                'day_of_week' => (int) $wh->day_of_week,
                'start_time' => (string) $wh->start_time,
                'end_time' => (string) $wh->end_time,
                'is_active' => (bool) $wh->is_active,
            ])->values()->all() ?? [],

            // ✅ Holidays
            $consultant->holidays?->map(fn ($h) => [
                'id' => $h->id,
                'holiday_date' => optional($h->holiday_date)->toDateString(),
                'name' => $h->name,
            ])->values()->all() ?? [],

            $consultant->created_at?->toDateTimeString(),
            $consultant->deleted_at?->toDateTimeString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,

            'display_name' => $this->display_name,
            'bio' => $this->bio,
            'email' => $this->email,
            'phone' => $this->phone,

            'years_of_experience' => $this->years_of_experience,
            'specialization_summary' => $this->specialization_summary,

            'profile_image' => $this->profile_image,
            'address' => $this->address,

            'governorate_id' => $this->governorate_id,
            'governorate_name_ar' => $this->governorate_name_ar,
            'governorate_name_en' => $this->governorate_name_en,

            'district_id' => $this->district_id,
            'district_name_ar' => $this->district_name_ar,
            'district_name_en' => $this->district_name_en,

            'area_id' => $this->area_id,
            'area_name_ar' => $this->area_name_ar,
            'area_name_en' => $this->area_name_en,

            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'is_active' => $this->is_active,

            // ✅ Working hours
            'working_hours' => $this->working_hours,
            'active_working_hours' => $this->active_working_hours,

            // ✅ Holidays
            'holidays' => $this->holidays,

            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_image' => $this->profile_image,
            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'is_active' => $this->is_active,

            'governorate_name_ar' => $this->governorate_name_ar,
            'governorate_name_en' => $this->governorate_name_en,

            'district_name_ar' => $this->district_name_ar,
            'district_name_en' => $this->district_name_en,
        ];
    }
}