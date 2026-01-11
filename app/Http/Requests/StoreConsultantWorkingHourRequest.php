<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultantWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // consultant_id سيتم تعيينه تلقائياً من الـ Controller
            // 0=Sunday ... 6=Saturday
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.required' => 'يوم الأسبوع مطلوب.',
            'day_of_week.integer' => 'يوم الأسبوع يجب أن يكون رقماً صحيحاً.',
            'day_of_week.min' => 'يوم الأسبوع يجب أن يكون بين 0 و 6.',
            'day_of_week.max' => 'يوم الأسبوع يجب أن يكون بين 0 و 6.',
            'start_time.required' => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'صيغة وقت البداية غير صحيحة. يجب أن تكون HH:MM.',
            'end_time.required' => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'صيغة وقت النهاية غير صحيحة. يجب أن تكون HH:MM.',
            'end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ];
    }
}
