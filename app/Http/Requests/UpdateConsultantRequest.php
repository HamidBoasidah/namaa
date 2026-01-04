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
        $consultantId = optional($this->route('consultant'))->id ?? $this->route('consultant');

        return [
            'user_id' => 'sometimes|required|exists:users,id',

            'display_name' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string',

            'email' => 'sometimes|nullable|email|max:255|unique:consultants,email,' . $consultantId,
            // الهاتف: يجب أن يكون 10 خانات ويبدأ بـ 05
            'phone' => ['sometimes', 'nullable', 'regex:/^05\\d{8}$/'],

            'years_of_experience' => 'sometimes|nullable|integer|min:0|max:80',
            'specialization_summary' => 'sometimes|nullable|string',

            'profile_image' => 'sometimes|nullable|file|image|max:2048',

            'address' => 'sometimes|nullable|string|max:255',

            'governorate_id' => 'sometimes|required|exists:governorates,id',
            'district_id' => 'sometimes|required|exists:districts,id',
            'area_id' => 'sometimes|required|exists:areas,id',

            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
