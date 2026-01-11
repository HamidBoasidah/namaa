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

}
