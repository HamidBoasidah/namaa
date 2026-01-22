<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the message resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender->name ?? null,
            'body' => $this->body,
            'type' => $this->type,
            'context' => $this->context,
            'attachments' => AttachmentResource::collection($this->attachments),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
