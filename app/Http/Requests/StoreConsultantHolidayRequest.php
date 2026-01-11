<?php

namespace App\Http\Requests;

use App\Models\Consultant;
use App\Models\ConsultantHoliday;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsultantHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // consultant_id سيتم تعيينه تلقائياً من الـ Controller
            'holiday_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $consultant = Consultant::where('user_id', $this->user()->id)->first();
                    if (!$consultant) {
                        return;
                    }

                    $exists = ConsultantHoliday::where('consultant_id', $consultant->id)
                        ->where('holiday_date', $value)
                        ->exists();

                    if ($exists) {
                        $fail('لا يمكن تكرار نفس تاريخ الإجازة.');
                    }
                },
            ],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'holiday_date.required' => 'تاريخ الإجازة مطلوب.',
            'holiday_date.date_format' => 'صيغة التاريخ يجب أن تكون YYYY-MM-DD.',
            'holiday_date.after_or_equal' => 'تاريخ الإجازة يجب أن يكون اليوم أو تاريخًا مستقبليًا.',
            'name.string' => 'اسم الإجازة يجب أن يكون نصاً.',
            'name.max' => 'اسم الإجازة يجب ألا يتجاوز 255 حرفاً.',
        ];
    }
}
