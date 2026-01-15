<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailableSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'bookable_type' => [
                'nullable',
                'string',
                'in:consultant,consultant_service',
            ],
            'bookable_id' => [
                'required_with:bookable_type',
                'nullable',
                'integer',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'التاريخ مطلوب.',
            'date.date_format' => 'صيغة التاريخ يجب أن تكون YYYY-MM-DD.',
            'date.after_or_equal' => 'التاريخ يجب أن يكون اليوم أو تاريخًا مستقبليًا.',
            'bookable_type.in' => 'نوع الحجز يجب أن يكون consultant أو consultant_service.',
            'bookable_id.required_with' => 'معرف الخدمة مطلوب عند تحديد نوع الحجز.',
        ];
    }
}
