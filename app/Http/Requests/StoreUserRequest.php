<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'avatar' => 'nullable|file|image|max:2048',
            'phone_number' => ['required', 'regex:/^05\\d{8}$/', 'unique:users,phone_number'],
            'whatsapp_number' => ['nullable', 'regex:/^05\\d{8}$/', 'unique:users,whatsapp_number'],
            'gender' => 'nullable|in:male,female',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:customer,consultant',
            // when user_type is consultant, consultation_type must be provided and exist
            'consultation_type_id' => 'required_if:user_type,consultant|nullable|exists:consultation_types,id',
            'years_of_experience' => 'nullable|integer|min:0',

            // optional certificate upload for consultants (image/pdf up to 5MB)
            'certificate' => 'required_if:user_type,consultant|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'is_active' => 'nullable|boolean',
            'locale' => 'nullable|string|max:10',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
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
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'user_type.required' => 'نوع المستخدم مطلوب.',
            'user_type.in' => 'نوع المستخدم غير صالح.',
            'consultation_type_id.required_if' => 'نوع الاستشارة مطلوب للمستشارين.',
            'consultation_type_id.exists' => 'نوع الاستشارة غير موجود.',
            'years_of_experience.integer' => 'سنوات الخبرة يجب أن تكون رقماً صحيحاً.',
            'years_of_experience.min' => 'سنوات الخبرة يجب أن تكون 0 أو أكثر.',
            'gender.in' => 'الجنس غير صالح.',
            'avatar.image' => 'الصورة يجب أن تكون ملف صورة.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
        ];
    }
}
