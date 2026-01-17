<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'consultant_id' => ['required', 'integer', 'exists:consultants,id'],
            'bookable_type' => ['required', 'string', Rule::in(['consultant', 'consultant_service'])],
            'bookable_id' => ['required', 'integer'],
            'start_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'buffer_after_minutes' => ['nullable', 'integer', 'min:0', 'max:60'],
            'consultation_method' => ['required_if:bookable_type,consultant', 'nullable', 'string', Rule::in(['video', 'audio', 'text'])],
            'status' => ['nullable', 'string', Rule::in([
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'العميل مطلوب',
            'client_id.exists' => 'العميل غير موجود',
            'consultant_id.required' => 'المستشار مطلوب',
            'consultant_id.exists' => 'المستشار غير موجود',
            'bookable_type.required' => 'نوع الحجز مطلوب',
            'bookable_type.in' => 'نوع الحجز غير صالح',
            'bookable_id.required' => 'معرف الخدمة مطلوب',
            'start_at.required' => 'وقت البداية مطلوب',
            'start_at.date' => 'وقت البداية غير صالح',
            'start_at.after' => 'وقت البداية يجب أن يكون في المستقبل',
            'duration_minutes.required' => 'المدة مطلوبة',
            'duration_minutes.min' => 'المدة يجب أن تكون 5 دقائق على الأقل',
            'duration_minutes.max' => 'المدة يجب ألا تتجاوز 8 ساعات',
            'buffer_after_minutes.min' => 'وقت الراحة يجب أن يكون 0 على الأقل',
            'buffer_after_minutes.max' => 'وقت الراحة يجب ألا يتجاوز 60 دقيقة',
            'consultation_method.required_if' => 'طريقة الاستشارة مطلوبة للحجز المباشر مع المستشار',
            'consultation_method.in' => 'طريقة الاستشارة يجب أن تكون video أو audio أو text',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ];
    }
}
