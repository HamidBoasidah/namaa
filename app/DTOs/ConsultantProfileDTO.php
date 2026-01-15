<?php

namespace App\DTOs;

use App\Models\User;
use App\Models\Consultant;

class ConsultantProfileDTO extends BaseDTO
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $avatar;
    public $gender;
    public $years_of_experience;
    public $consultation_type;
    public $price_per_hour;
    public $buffer;

    public function __construct(
        $id,
        $first_name,
        $last_name,
        $email,
        $phone_number,
        $avatar,
        $gender,
        $years_of_experience,
        $consultation_type,
        $price_per_hour = 0,
        $buffer = 0
    ) {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->avatar = $avatar;
        $this->gender = $gender;
        $this->years_of_experience = $years_of_experience;
        $this->consultation_type = $consultation_type;
        $this->price_per_hour = $price_per_hour;
        $this->buffer = $buffer;
    }

    public static function fromUser(User $user, ?Consultant $consultant = null): self
    {
        $consultationType = null;
        if ($consultant && $consultant->consultationType) {
            $consultationType = [
                'id' => $consultant->consultationType->id,
                'name' => $consultant->consultationType->name,
            ];
        }

        return new self(
            $user->id,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->phone_number,
            $user->avatar,
            $user->gender,
            $consultant?->years_of_experience,
            $consultationType
            , $consultant?->price_per_hour ?? 0,
            $consultant?->buffer ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'gender' => $this->gender,
            'years_of_experience' => $this->years_of_experience,
            'consultation_type' => $this->consultation_type,
            'price_per_hour' => $this->price_per_hour,
            'buffer' => $this->buffer,
        ];
    }
}
