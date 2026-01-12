<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = optional($this->route('user'))->id ?? $this->route('user');

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'avatar' => 'nullable|file|image|max:2048',
            'phone_number' => ['sometimes', 'regex:/^05\\d{8}$/', 'unique:users,phone_number,' . $userId],
            'whatsapp_number' => ['sometimes', 'regex:/^05\\d{8}$/', 'unique:users,whatsapp_number,' . $userId],
            'password' => 'nullable|string|min:8',
            'gender' => 'nullable|in:male,female',
            'user_type' => 'sometimes|in:customer,consultant',
            'price_per_hour' => 'sometimes|nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'locale' => 'nullable|string|max:10',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
