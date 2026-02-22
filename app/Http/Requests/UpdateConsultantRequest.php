<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consultation_type_id' => 'sometimes|required|exists:consultation_types,id',
            'years_of_experience' => 'sometimes|nullable|integer|min:0|max:80',
            'price' => 'sometimes|nullable|numeric|min:0',
            'duration_minutes' => 'sometimes|nullable|integer|min:1',
            'buffer' => 'sometimes|nullable|integer|min:0|max:1440',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }

}
