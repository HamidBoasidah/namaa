<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('consultation_type') ? $this->route('consultation_type')->id : null;
        return [
            'name' => 'sometimes|required|string|max:255|unique:consultation_types,name,' . $id,
            'slug' => 'sometimes|nullable|string|max:255|unique:consultation_types,slug,' . $id,
            'is_active' => 'nullable|boolean',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
