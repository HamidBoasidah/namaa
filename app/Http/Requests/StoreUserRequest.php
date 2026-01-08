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
            'phone_number' => ['nullable', 'regex:/^\\d{9,15}$/'],
            'gender' => 'nullable|in:male,female',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:customer,consultant',
            // when user_type is consultant, consultation_type must be provided and exist
            'consultation_type_id' => 'required_if:user_type,consultant|nullable|exists:consultation_types,id',
            'is_active' => 'nullable|boolean',
            'locale' => 'nullable|string|max:10',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
