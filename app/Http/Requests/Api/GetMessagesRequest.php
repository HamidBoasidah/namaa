<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursor' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'cursor.string' => 'The cursor must be a valid string.',
            'per_page.integer' => 'The per_page parameter must be an integer.',
            'per_page.min' => 'The per_page parameter must be at least 1.',
            'per_page.max' => 'The per_page parameter must not exceed 100.',
        ];
    }
}
