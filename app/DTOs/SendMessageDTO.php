<?php

namespace App\DTOs;

use App\Exceptions\ValidationException;
use Illuminate\Http\UploadedFile;

class SendMessageDTO extends BaseDTO
{
    public int $conversation_id;
    public int $sender_id;
    public ?string $body;
    public array $files;

    /**
     * Create a new SendMessageDTO instance with validation
     *
     * @param int $conversation_id
     * @param int $sender_id
     * @param string|null $body
     * @param array $files Array of UploadedFile instances
     * @throws ValidationException
     */
    public function __construct(
        int $conversation_id,
        int $sender_id,
        ?string $body,
        array $files = []
    ) {
        $this->validate($body, $files);

        $this->conversation_id = $conversation_id;
        $this->sender_id = $sender_id;
        $this->body = $body;
        $this->files = $files;
    }

    /**
     * Validate the message data
     *
     * @param string|null $body
     * @param array $files
     * @throws ValidationException
     */
    private function validate(?string $body, array $files): void
    {
        // At least one of body or files must be present
        if (empty($body) && empty($files)) {
            throw ValidationException::withMessages([
                'message' => ['Either message body or files must be provided.']
            ]);
        }

        // Validate that files array contains only UploadedFile instances
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile)) {
                throw ValidationException::withMessages([
                    'files' => ['All files must be valid uploaded file instances.']
                ]);
            }
        }
    }
}
