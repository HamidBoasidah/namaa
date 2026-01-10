<?php

namespace App\DTOs;

use App\Models\Certificate;

class CertificateDTO extends BaseDTO
{
    public $id;
    public $consultant_id;
    public $status;
    public $rejected_reason;
    public $verified_at;
    public $document_scan_copy;
    public $document_scan_copy_original_name;
    public $is_verified;
    
    public $created_at;
    public $deleted_at;
    public $consultant;

    public function __construct(
        $id,
        $consultant_id,
        $status,
        $rejected_reason,
        $verified_at,
        $document_scan_copy,
        $document_scan_copy_original_name,
        $is_verified,
        $created_at = null,
        $deleted_at = null,
        $consultant = null
    ) {
        $this->id = $id;
        $this->consultant_id = $consultant_id;
        $this->status = $status;
        $this->rejected_reason = $rejected_reason;
        $this->verified_at = $verified_at;
        $this->document_scan_copy = $document_scan_copy;
        $this->document_scan_copy_original_name = $document_scan_copy_original_name;
        $this->is_verified = (bool) $is_verified;
        $this->created_at = $created_at;
        $this->deleted_at = $deleted_at;
        $this->consultant = $consultant;
    }

    public static function fromModel(Certificate $certificate): self
    {
        $consultantData = null;
        if ($certificate->consultant && $certificate->consultant->user) {
            $user = $certificate->consultant->user;
            $consultantData = [
                'id' => $certificate->consultant->id,
                'user_id' => $certificate->consultant->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'avatar' => $user->avatar,
                'gender' => $user->gender,
            ];
        }

        return new self(
            $certificate->id,
            $certificate->consultant_id ?? null,
            $certificate->status ?? null,
            $certificate->rejected_reason ?? null,
            $certificate->verified_at?->toDateTimeString() ?? null,
            $certificate->document_scan_copy ?? null,
            $certificate->document_scan_copy_original_name ?? null,
            $certificate->is_verified ?? false,
            $certificate->created_at?->toDateTimeString() ?? null,
            $certificate->deleted_at?->toDateTimeString() ?? null,
            $consultantData,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'consultant_id' => $this->consultant_id,
            'status' => $this->status,
            'rejected_reason' => $this->rejected_reason,
            'verified_at' => $this->verified_at,
            'document_scan_copy' => $this->document_scan_copy,
            'document_scan_copy_original_name' => $this->document_scan_copy_original_name,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'consultant' => $this->consultant,
        ];
    }

    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'consultant_id' => $this->consultant_id,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
        ];
    }
}
