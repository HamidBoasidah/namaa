<?php

namespace App\Http\Requests\Api;

use App\Models\Consultant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConsultantServiceRequest extends FormRequest
{
    protected ?Consultant $consultant = null;

    public function authorize(): bool
    {
        $this->consultant = Consultant::where('user_id', $this->user()->id)->first();
        return $this->consultant !== null;
    }

    public function rules(): array
    {
        $consultantId = $this->consultant?->id;

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'title' => [
                'required',
                'string',
                'max:255',
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
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'buffer' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'consultation_method' => ['nullable', 'in:video,audio,text'],
            'delivery_time' => ['nullable', 'string', 'max:255'],
            'auto_accept_requests' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'file', 'mimes:jpeg,png,gif,svg,webp', 'max:2048'],
            'includes' => ['nullable', 'array'],
            'includes.*' => ['string', 'max:500'],
            'target_audience' => ['nullable', 'array'],
            'target_audience.*' => ['string', 'max:500'],
            'deliverables' => ['nullable', 'array'],
            'deliverables.*' => ['string', 'max:500'],
        ];
    }

    public function getConsultant(): ?Consultant
    {
        return $this->consultant;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('duration_minutes') && $this->input('duration_minutes') === '') {
            $this->merge(['duration_minutes' => null]);
        }
        if ($this->has('buffer') && $this->input('buffer') === '') {
            $this->merge(['buffer' => null]);
        }
    }
}
