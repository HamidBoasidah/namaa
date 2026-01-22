<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the conversation resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'participants' => $this->participants->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? null,
                    'avatar' => $user->avatar ?? null,
                ];
            })->values(),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
