<?php

namespace App\DTOs;

use App\Models\User;
 
class UserDTO extends BaseDTO
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $address;
    public $phone_number;
    public $user_type;
    public $gender;
    public $consultation_type;
    public $is_active;
    public $locale;
    public $avatar;
    
    public $created_by;
    public $updated_by;
    public $created_at;
    public $deleted_at;

    public function __construct($id, $first_name, $last_name, $email, $phone_number, $user_type, $gender, $consultation_type, $is_active, $locale, $avatar, $created_by, $updated_by, $created_at = null, $deleted_at = null)
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->user_type = $user_type;
        $this->gender = $gender;
        $this->consultation_type = $consultation_type;
        $this->is_active = $is_active;
        $this->locale = $locale;
        $this->avatar = $avatar;
        $this->created_by = $created_by;
        $this->updated_by = $updated_by;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->first_name ?? null,
            $user->last_name ?? null,
            $user->email ?? null,
            $user->phone_number ?? null,
            $user->user_type ?? 'customer',
            $user->gender ?? null,
            $user->consultation_type ?? null,
            (bool) ($user->is_active ?? false),
            $user->locale ?? null,
            $user->avatar ?? null,
            $user->created_by ?? null,
            $user->updated_by ?? null,
            $user->created_at?->toDateTimeString() ?? null,
            $user->deleted_at?->toDateTimeString() ?? null
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
            'user_type' => $this->user_type,
            'gender' => $this->gender,
            'consultation_type' => $this->consultation_type,
            'is_active' => $this->is_active,
            'locale' => $this->locale,
            'avatar' => $this->avatar,
            
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'user_type' => $this->user_type,
            'gender' => $this->gender,
            'consultation_type' => $this->consultation_type,
            'is_active' => $this->is_active,
            'avatar' => $this->avatar,
            
        ];
    }
}
