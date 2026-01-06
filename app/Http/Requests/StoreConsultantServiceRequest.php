<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultantServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $consultantId = $this->input('consultant_id');

        return [
            'consultant_id' => ['required', 'integer', 'exists:consultants,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            'title' => [
                'required',
                'string',
                'max:255',
                // ✅ منع تكرار العنوان لنفس المستشار (مع تجاهل المحذوفات Soft Deleted)
                Rule::unique('consultant_services', 'title')
                    ->where(function ($q) use ($consultantId) {
                        return $q->where('consultant_id', $consultantId)
                                 ->whereNull('deleted_at');
                    }),
            ],

            'description' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],

            'price' => ['required', 'numeric', 'min:0'],

            // بما أنك حاط default=60 في DB:
            // - نقدر نخليه nullable، ولو انرسل نتأكد أنه رقم
            // - أو نجبره يكون 60 دائمًا (اختياري)
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // تحسين بسيط: لو أرسل duration_minutes فاضي، نخليه null حتى ياخذ default من DB
        if ($this->has('duration_minutes') && $this->input('duration_minutes') === '') {
            $this->merge(['duration_minutes' => null]);
        }
    }
}