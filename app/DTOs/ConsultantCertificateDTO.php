<?php

namespace App\DTOs;

use App\Models\Certificate;

class ConsultantCertificateDTO extends BaseDTO
{
    public $id;
    public $consultant_id;
    public $status;
    public $document_scan_copy;
    public $document_scan_copy_original_name;
    public $rejected_reason;
    public $created_at;

    public function __construct(
        $id,
        $consultant_id,
        $status,
        $document_scan_copy,
        $document_scan_copy_original_name,
        $rejected_reason,
        $created_at
    ) {
        $this->id = $id;
        $this->consultant_id = $consultant_id;
        $this->status = $status;
        $this->document_scan_copy = $document_scan_copy;
        $this->document_scan_copy_original_name = $document_scan_copy_original_name;
        $this->rejected_reason = $rejected_reason;
        $this->created_at = $created_at;
    }

    public static function fromModel(Certificate $certificate): self
    {
        return new self(
            $certificate->id,
            $certificate->consultant_id,
            $certificate->status,
            $certificate->document_scan_copy ? true : false,
            $certificate->document_scan_copy_original_name,
            $certificate->rejected_reason,
            $certificate->created_at?->toDateTimeString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'consultant_id' => $this->consultant_id,
            'status' => $this->status,
            'document_scan_copy' => $this->document_scan_copy,
            'document_name' => $this->document_scan_copy_original_name,
            'rejected_reason' => $this->rejected_reason,
            'created_at' => $this->created_at,
        ];
    }
}
