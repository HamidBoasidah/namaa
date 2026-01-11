<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationException as AppValidationException;

class UpdateConsultantExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:500',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال وصف الخبرة',
            'name.max' => 'وصف الخبرة يجب ألا يتجاوز 500 حرف',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw AppValidationException::withMessages($validator->errors()->toArray());
    }
}
