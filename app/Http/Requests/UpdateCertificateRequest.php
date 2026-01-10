<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationException as AppValidationException;

class UpdateCertificateRequest extends FormRequest
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
            'consultant_id' => 'nullable|exists:consultants,id',
            'status' => 'nullable|in:pending,approved,rejected',
            'rejected_reason' => 'nullable|string|max:1000',
            'is_verified' => 'nullable|boolean',
            'verified_at' => 'nullable|date',
            // document_scan_copy may be optional on update
            'document_scan_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
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
