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

            'display_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',

            'email' => 'nullable|email|max:255|unique:consultants,email',
            // الهاتف: يجب أن يكون 10 خانات ويبدأ بـ 05
            'phone' => ['nullable', 'regex:/^05\\d{8}$/'],

            'years_of_experience' => 'nullable|integer|min:0|max:80',
            'specialization_summary' => 'nullable|string',

            'profile_image' => 'nullable|file|image|max:2048',

            'address' => 'nullable|string|max:255',

            'governorate_id' => 'required|exists:governorates,id',
            'district_id' => 'required|exists:districts,id',
            'area_id' => 'required|exists:areas,id',

            'is_active' => 'nullable|boolean',
        ];
    }
}
