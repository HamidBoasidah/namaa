<?php

namespace App\DTOs;

use App\Models\Consultant;

class ConsultantDTO extends BaseDTO
{
    public $id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $user_name;
    public $user_email;
    public $user_phone;
    public $avatar;
    public $gender;
    public $price_per_hour;
    public $buffer;

    public $years_of_experience;

    public $consultation_type_id;
    public $consultation_type_name;

    public $rating_avg;
    public $ratings_count;

    public $is_active;

    public array $working_hours = [];
    public array $active_working_hours = [];

    public array $holidays = [];

    public array $experiences = [];

    public $created_at;
    public $deleted_at;

    public function __construct(
        $id,
        $user_id,
        $first_name,
        $last_name,
        $user_name,
        $user_email,
        $user_phone,
        $avatar,

        $gender,
        $years_of_experience,

        $consultation_type_id,
        $consultation_type_name,
        $price_per_hour,
        $buffer,

        $rating_avg,
        $ratings_count,
        $is_active,

        array $working_hours = [],
        array $active_working_hours = [],

        array $holidays = [],

        array $experiences = [],

        $created_at = null,
        $deleted_at = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->user_name = $user_name;
        $this->user_email = $user_email;
        $this->user_phone = $user_phone;
        $this->avatar = $avatar;

        $this->gender = $gender;

        $this->years_of_experience = $years_of_experience;

        $this->consultation_type_id = $consultation_type_id;
        $this->consultation_type_name = $consultation_type_name;

        $this->price_per_hour = $price_per_hour;
        $this->buffer = $buffer;

        $this->rating_avg = $rating_avg;
        $this->ratings_count = $ratings_count;
        $this->is_active = $is_active;

        $this->working_hours = $working_hours;
        $this->active_working_hours = $active_working_hours;

        $this->holidays = $holidays;

        $this->experiences = $experiences;

        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(Consultant $consultant): self
    {
        return new self(
            $consultant->id,
            $consultant->user_id,
            $consultant->user?->first_name,
            $consultant->user?->last_name,
            $consultant->user?->name,
            $consultant->user?->email,
            $consultant->user?->phone_number,

            $consultant->user?->avatar ?? null,
            $consultant->user?->gender ?? null,
            $consultant->years_of_experience,

            $consultant->consultation_type_id ?? null,
            $consultant->consultationType?->name ?? null,

            (float) ($consultant->price_per_hour ?? 0),
            (int) ($consultant->buffer ?? 0),
            (float) ($consultant->rating_avg ?? 0),
            (int) ($consultant->ratings_count ?? 0),
            (bool) ($consultant->is_active ?? false),

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

            $consultant->holidays?->map(fn ($h) => [
                'id' => $h->id,
                'holiday_date' => optional($h->holiday_date)->toDateString(),
                'name' => $h->name,
            ])->values()->all() ?? [],

            $consultant->experiences?->map(fn ($exp) => [
                'id' => $exp->id,
                'name' => $exp->name,
                'is_active' => (bool) ($exp->is_active ?? true),
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'avatar' => $this->avatar,
            'gender' => $this->gender,

            'years_of_experience' => $this->years_of_experience,

            'consultation_type_id' => $this->consultation_type_id,
            'consultation_type_name' => $this->consultation_type_name,

            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'is_active' => $this->is_active,
            'price_per_hour' => $this->price_per_hour,
            'buffer' => $this->buffer,

            'working_hours' => $this->working_hours,
            'active_working_hours' => $this->active_working_hours,

            'holidays' => $this->holidays,

            'experiences' => $this->experiences,

            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'avatar' => $this->avatar,

            'consultation_type_name' => $this->consultation_type_name,

            'gender' => $this->gender,

            'rating_avg' => $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'is_active' => $this->is_active,
        ];
    }
}