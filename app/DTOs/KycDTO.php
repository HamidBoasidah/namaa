<?php

namespace App\DTOs;

use App\Models\Kyc;

class KycDTO extends BaseDTO
{
    public $id;
    public $user_id;
    public $status;
    public $rejected_reason;
    public $verified_at;
    public $full_name;
    public $gender;
    public $date_of_birth;
    public $address;
    public $document_type;
    public $document_scan_copy;
    public $is_verified;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $deleted_at;
    public $user;

    public function __construct(
        $id,
        $user_id,
        $status,
        $rejected_reason,
        $verified_at,
        $full_name,
        $gender,
        $date_of_birth,
        $address,
        $document_type,
        $document_scan_copy,
        $is_verified,
        $created_by,
        $updated_by,
        $created_at = null,
        $deleted_at = null,
        $user = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->status = $status;
        $this->rejected_reason = $rejected_reason;
        $this->verified_at = $verified_at;
        $this->full_name = $full_name;
        $this->gender = $gender;
        $this->date_of_birth = $date_of_birth;
        $this->address = $address;
        $this->document_type = $document_type;
        $this->document_scan_copy = $document_scan_copy;
        $this->is_verified = (bool) $is_verified;
        $this->created_by = $created_by;
        $this->updated_by = $updated_by;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
        $this->user = $user;
    }

    public static function fromModel(Kyc $kyc): self
    {
        $userData = $kyc->user
            ? $kyc->user->only(['id', 'first_name', 'last_name', 'email', 'phone_number', 'avatar'])
            : null;

        return new self(
            $kyc->id,
            $kyc->user_id ?? null,
            $kyc->status ?? null,
            $kyc->rejected_reason ?? null,
            $kyc->verified_at?->toDateTimeString() ?? null,
            $kyc->full_name ?? null,
            $kyc->gender ?? null,
            $kyc->date_of_birth?->toDateString() ?? null,
            $kyc->address ?? null,
            $kyc->document_type ?? null,
            $kyc->document_scan_copy ?? null,
            $kyc->is_verified ?? false,
            $kyc->created_by ?? null,
            $kyc->updated_by ?? null,
            $kyc->created_at?->toDateTimeString() ?? null,
            $kyc->deleted_at?->toDateTimeString() ?? null,
            $userData,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'rejected_reason' => $this->rejected_reason,
            'verified_at' => $this->verified_at,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'address' => $this->address,
            'document_type' => $this->document_type,
            'document_scan_copy' => $this->document_scan_copy,
            'is_verified' => $this->is_verified,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'user' => $this->user,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
        ];
    }
}
