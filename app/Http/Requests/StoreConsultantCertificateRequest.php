<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationException as AppValidationException;

class StoreConsultantCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_scan_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'document_scan_copy.required' => 'يرجى رفع ملف الشهادة',
            'document_scan_copy.file' => 'يجب أن يكون ملفاً صالحاً',
            'document_scan_copy.mimes' => 'يجب أن يكون الملف من نوع: jpg, jpeg, png, pdf',
            'document_scan_copy.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw AppValidationException::withMessages($validator->errors()->toArray());
    }
}
