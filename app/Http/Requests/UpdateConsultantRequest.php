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
        $consultant = $this->route('consultant');
        $userId = $consultant?->user_id;

        return [
            // بيانات المستخدم (من جدول users)
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $userId,
            'phone_number' => ['sometimes', 'required', 'regex:/^05\\d{8}$/', 'unique:users,phone_number,' . $userId],
            'gender' => 'sometimes|nullable|in:male,female',
            'avatar' => 'sometimes|nullable|file|image|max:2048',

            // بيانات المستشار (من جدول consultants)
            'consultation_type_id' => 'sometimes|required|exists:consultation_types,id',
            'years_of_experience' => 'sometimes|nullable|integer|min:0|max:80',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            'phone_number.required' => 'رقم الهاتف مطلوب.',
            'phone_number.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'phone_number.unique' => 'رقم الهاتف مستخدم بالفعل.',
            'gender.in' => 'الجنس غير صالح.',
            'avatar.image' => 'الصورة يجب أن تكون ملف صورة.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
            'consultation_type_id.required' => 'نوع الاستشارة مطلوب.',
            'consultation_type_id.exists' => 'نوع الاستشارة غير موجود.',
            'years_of_experience.integer' => 'سنوات الخبرة يجب أن تكون رقماً صحيحاً.',
            'years_of_experience.min' => 'سنوات الخبرة يجب أن تكون 0 أو أكثر.',
        ];
    }
}
