<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxFiles = config('chat.attachments.max_files_per_message', 5);
        $maxFileSizeMb = config('chat.attachments.max_file_size_mb', 25);
        $maxFileSizeKb = $maxFileSizeMb * 1024;
        $allowedMimeTypes = config('chat.attachments.allowed_mime_types', []);
        $maxMessageLength = config('chat.limits.max_message_length', 5000);

        return [
            'body' => ['nullable', 'string', "max:{$maxMessageLength}"],
            'files' => ['nullable', 'array', "max:{$maxFiles}"],
            'files.*' => [
                'file',
                "max:{$maxFileSizeKb}",
                function ($attribute, $value, $fail) use ($allowedMimeTypes) {
                    if ($value && !in_array($value->getMimeType(), $allowedMimeTypes)) {
                        $fail("نوع الملف {$value->getMimeType()} غير مسموح.");
                    }
                },
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $body = $this->input('body');
            $files = $this->file('files');

            // At least one of body or files must be present
            if (empty($body) && empty($files)) {
                $validator->errors()->add(
                    'message',
                    'يجب تقديم نص الرسالة أو ملفات على الأقل.'
                );
            }
        });
    }

    public function messages(): array
    {
        $maxFiles = config('chat.attachments.max_files_per_message', 5);
        $maxFileSizeMb = config('chat.attachments.max_file_size_mb', 25);
        $maxMessageLength = config('chat.limits.max_message_length', 5000);

        return [
            'body.string' => 'يجب أن يكون نص الرسالة نصاً.',
            'body.max' => "يجب ألا يتجاوز نص الرسالة {$maxMessageLength} حرف.",
            'files.array' => 'يجب تقديم الملفات كمصفوفة.',
            'files.max' => "يمكنك رفع {$maxFiles} ملفات كحد أقصى لكل رسالة.",
            'files.*.file' => 'يجب أن يكون كل رفع ملفاً صالحاً.',
            'files.*.max' => "يجب ألا يتجاوز حجم كل ملف {$maxFileSizeMb} ميجابايت.",
            'files.*.mimes' => 'ملف واحد أو أكثر له نوع ملف غير صالح. يرجى التحقق من أنواع الملفات المسموح بها.',
        ];
    }
}
