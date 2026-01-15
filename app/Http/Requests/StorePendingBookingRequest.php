<?php

namespace App\Http\Requests;

use App\Models\Consultant;
use App\Models\ConsultantService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePendingBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            
            // فقط العملاء يمكنهم إنشاء حجوزات
            // المستشارون لا يمكنهم حجز مستشارين آخرين
            if ($user && $user->user_type === 'consultant') {
                $validator->errors()->add('user', 'المستشارون لا يمكنهم إنشاء حجوزات. يجب تسجيل الدخول كعميل.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'consultant_id' => [
                'required',
                'integer',
                'exists:consultants,id',
            ],
            'bookable_type' => [
                'required',
                'string',
                'in:consultant,consultant_service',
            ],
            'bookable_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $this->validateBookable($value, $fail);
                },
            ],
            'start_at' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $this->validateTimeGranularity($value, $fail);
                },
            ],
            'duration_minutes' => [
                'required_if:bookable_type,consultant',
                'nullable',
                'integer',
                'min:5',
                'max:480',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value % 5 !== 0) {
                        $fail('المدة يجب أن تكون من مضاعفات 5 دقائق.');
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Validate that start_at is a multiple of 5 minutes
     */
    protected function validateTimeGranularity($value, $fail): void
    {
        try {
            $time = Carbon::parse($value);
            if ($time->minute % 5 !== 0) {
                $fail('وقت البداية يجب أن يكون من مضاعفات 5 دقائق.');
            }
        } catch (\Exception $e) {
            $fail('صيغة التاريخ غير صالحة.');
        }
    }

    /**
     * Validate that bookable exists and belongs to consultant
     */
    protected function validateBookable($value, $fail): void
    {
        $bookableType = $this->input('bookable_type');
        $consultantId = $this->input('consultant_id');

        if ($bookableType === 'consultant') {
            // For consultant booking, bookable_id must match consultant_id
            if ((int) $value !== (int) $consultantId) {
                $fail('معرف المستشار غير متطابق.');
                return;
            }

            if (!Consultant::where('id', $value)->where('is_active', true)->exists()) {
                $fail('المستشار غير موجود أو غير متاح.');
            }
        } elseif ($bookableType === 'consultant_service') {
            // For service booking, service must exist and belong to consultant
            $service = ConsultantService::find($value);
            
            if (!$service) {
                $fail('الخدمة غير موجودة.');
                return;
            }

            if ($service->consultant_id !== (int) $consultantId) {
                $fail('الخدمة لا تنتمي لهذا المستشار.');
                return;
            }

            if (!$service->is_active) {
                $fail('الخدمة غير متاحة حالياً.');
            }
        }
    }

    public function messages(): array
    {
        return [
            'consultant_id.required' => 'معرف المستشار مطلوب.',
            'consultant_id.exists' => 'المستشار غير موجود.',
            'bookable_type.required' => 'نوع الحجز مطلوب.',
            'bookable_type.in' => 'نوع الحجز يجب أن يكون consultant أو consultant_service.',
            'bookable_id.required' => 'معرف الخدمة أو المستشار مطلوب.',
            'start_at.required' => 'وقت البداية مطلوب.',
            'start_at.date' => 'صيغة التاريخ غير صالحة.',
            'start_at.after' => 'وقت البداية يجب أن يكون في المستقبل.',
            'duration_minutes.required_if' => 'المدة مطلوبة للحجز المباشر مع المستشار.',
            'duration_minutes.integer' => 'المدة يجب أن تكون رقماً صحيحاً.',
            'duration_minutes.min' => 'المدة يجب أن تكون 5 دقائق على الأقل.',
            'duration_minutes.max' => 'المدة يجب ألا تتجاوز 480 دقيقة (8 ساعات).',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}
