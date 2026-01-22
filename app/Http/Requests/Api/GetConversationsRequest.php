<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetConversationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'يجب أن يكون البحث نصاً.',
            'search.max' => 'يجب ألا يتجاوز البحث 255 حرف.',
            'per_page.integer' => 'يجب أن يكون عدد العناصر في الصفحة رقماً صحيحاً.',
            'per_page.min' => 'يجب أن يكون عدد العناصر في الصفحة 1 على الأقل.',
            'per_page.max' => 'يجب ألا يتجاوز عدد العناصر في الصفحة 100.',
        ];
    }
}
