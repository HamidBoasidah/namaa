<?php

namespace App\Http\Requests;

use App\Models\ConsultantWorkingHour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultantWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consultant_id' => ['required', 'integer', 'exists:consultants,id'],

            // 0=Sunday ... 6=Saturday
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],

            'is_active' => ['nullable', 'boolean'],

            // ✅ منع تكرار نفس الفترة تمامًا (DB-level unique موجود + هنا validation لطيف)
            // ملاحظة: هذا يمنع فقط (نفس start/end)، أما التداخل نمنعه بالـ closure أدناه
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('consultant_working_hours', 'start_time')->where(function ($q) {
                    return $q->where('consultant_id', $this->input('consultant_id'))
                        ->where('day_of_week', $this->input('day_of_week'))
                        ->where('start_time', $this->input('start_time'))
                        ->where('end_time', $this->input('end_time'));
                }),
            ],

            // ✅ منع التداخل (Overlapping)
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    $consultantId = (int) $this->input('consultant_id');
                    $day          = (int) $this->input('day_of_week');
                    $start        = $this->input('start_time');
                    $end          = $this->input('end_time');

                    $overlapExists = ConsultantWorkingHour::query()
                        ->where('consultant_id', $consultantId)
                        ->where('day_of_week', $day)
                        // overlap: new_start < existing_end AND new_end > existing_start
                        ->where('start_time', '<', $end)
                        ->where('end_time', '>', $start)
                        ->exists();

                    if ($overlapExists) {
                        $fail('يوجد تداخل مع فترة عمل أخرى في نفس اليوم لهذا المستشار.');
                    }
                },
            ],
        ];
    }

}