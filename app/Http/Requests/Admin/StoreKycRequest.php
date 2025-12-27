<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'rejected_reason' => 'nullable|string|required_if:status,rejected',
            'is_verified' => 'nullable|boolean',
            'verified_at' => 'nullable|date',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'document_type' => 'required|in:passport,driving_license,id_card',
            'document_scan_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
