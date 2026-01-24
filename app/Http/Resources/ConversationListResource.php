<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the other participant (not the current user)
        $currentUserId = $request->user()->id;
        $otherParticipant = $this->participants->firstWhere('id', '!=', $currentUserId);

        // Get last message
        $lastMessage = $this->messages->first();

        // Get unread count for current user
        $unreadCount = app(\App\Services\ReadStateService::class)
            ->getUnreadCount($this->id, $currentUserId);

        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'other_participant' => [
                'id' => $otherParticipant?->id,
                'full_name' => $otherParticipant ? trim($otherParticipant->first_name . ' ' . $otherParticipant->last_name) : null,
                'avatar' => $otherParticipant?->avatar,
            ],
            'last_message' => $lastMessage ? [
                'id' => $lastMessage->id,
                'body' => $lastMessage->body,
                'type' => $lastMessage->type,
                'sender_id' => $lastMessage->sender_id,
                'is_from_me' => $lastMessage->sender_id === $currentUserId,
                'created_at' => $lastMessage->created_at?->toIso8601String(),
            ] : null,
            'unread_count' => $unreadCount,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
