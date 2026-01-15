<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'buffer_after_minutes' => ['nullable', 'integer', 'min:0', 'max:60'],
            'status' => ['nullable', 'string', Rule::in([
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_COMPLETED,
                Booking::STATUS_CANCELLED,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_at.required' => 'وقت البداية مطلوب',
            'start_at.date' => 'وقت البداية غير صالح',
            'duration_minutes.required' => 'المدة مطلوبة',
            'duration_minutes.min' => 'المدة يجب أن تكون 5 دقائق على الأقل',
            'duration_minutes.max' => 'المدة يجب ألا تتجاوز 8 ساعات',
            'buffer_after_minutes.min' => 'وقت الراحة يجب أن يكون 0 على الأقل',
            'buffer_after_minutes.max' => 'وقت الراحة يجب ألا يتجاوز 60 دقيقة',
            'status.in' => 'الحالة غير صالحة',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ];
    }
}
