<?php

namespace App\Http\Requests;

use App\Models\ConsultantWorkingHour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsultantWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * محاولة جلب ID السجل الجاري تعديله من Route Model Binding
     * غيّر أسماء route params هنا إذا كانت مختلفة عندك.
     */
    protected function currentWorkingHourId(): ?int
    {
        // أمثلة محتملة لأسماء بارامتر الراوت
        $candidates = [
            'consultant_working_hour',
            'consultantWorkingHour',
            'working_hour',
            'workingHour',
        ];

        foreach ($candidates as $key) {
            $param = $this->route($key);
            if ($param instanceof ConsultantWorkingHour) {
                return $param->id;
            }
            if (is_numeric($param)) {
                return (int) $param;
            }
        }

        return null;
    }

    public function rules(): array
    {
        $id = $this->currentWorkingHourId();

        return [
            'consultant_id' => ['required', 'integer', 'exists:consultants,id'],
            'day_of_week'   => ['required', 'integer', 'min:0', 'max:6'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],

            'is_active' => ['nullable', 'boolean'],

            // ✅ منع تكرار نفس الفترة تمامًا (مع تجاهل السجل الحالي)
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('consultant_working_hours', 'start_time')
                    ->ignore($id)
                    ->where(function ($q) {
                        return $q->where('consultant_id', $this->input('consultant_id'))
                            ->where('day_of_week', $this->input('day_of_week'))
                            ->where('start_time', $this->input('start_time'))
                            ->where('end_time', $this->input('end_time'));
                    }),
            ],

            // ✅ منع التداخل (مع استثناء السجل الحالي)
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) use ($id) {
                    $consultantId = (int) $this->input('consultant_id');
                    $day          = (int) $this->input('day_of_week');
                    $start        = $this->input('start_time');
                    $end          = $this->input('end_time');

                    $overlapExists = ConsultantWorkingHour::query()
                        ->where('consultant_id', $consultantId)
                        ->where('day_of_week', $day)
                        ->when($id, fn($q) => $q->where('id', '!=', $id))
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

    public function messages(): array
    {
        return [
            'consultant_id.required' => 'المستشار مطلوب.',
            'consultant_id.exists'   => 'المستشار غير موجود.',
            'day_of_week.required'   => 'اليوم مطلوب.',
            'start_time.required'    => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'صيغة وقت البداية يجب أن تكون HH:MM.',
            'start_time.unique'      => 'هذه الفترة موجودة مسبقًا بنفس البداية والنهاية.',
            'end_time.required'      => 'وقت النهاية مطلوب.',
            'end_time.date_format'   => 'صيغة وقت النهاية يجب أن تكون HH:MM.',
            'end_time.after'         => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ];
    }
}