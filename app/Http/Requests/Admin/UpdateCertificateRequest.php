<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:pending,approved,rejected',
            'rejected_reason' => 'nullable|string|required_if:status,rejected',
            'is_verified' => 'nullable|boolean',
            'verified_at' => 'nullable|date',
            'full_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'document_type' => 'nullable|in:passport,driving_license,id_card',
            // document_scan_copy is required (cannot be null) on update
            'document_scan_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
