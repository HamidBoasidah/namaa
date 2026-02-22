<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'consultation_type_id' => 'required|exists:consultation_types,id',
            'years_of_experience' => 'nullable|integer|min:0|max:80',
            'price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'buffer' => 'nullable|integer|min:0|max:1440',
            'is_active' => 'nullable|boolean',
        ];
    }
}
