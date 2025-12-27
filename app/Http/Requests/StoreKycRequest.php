<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationException as AppValidationException;

class StoreKycRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            // status is managed by admins only and must not be accepted from API users
            // Do not include 'status' or 'rejected_reason' here so clients cannot set it.
            'is_verified' => 'nullable|boolean',
            'verified_at' => 'nullable|date',
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'document_type' => 'required|in:passport,driving_license,id_card',
            // accept images and pdfs for document scans
            'document_scan_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Convert failed validation into our application ValidationException so
     * API responses keep a consistent JSON shape.
     */
    protected function failedValidation(Validator $validator)
    {
        throw AppValidationException::withMessages($validator->errors()->toArray());
    }
}
