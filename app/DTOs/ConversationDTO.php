<?php

namespace App\DTOs;

use App\Models\Conversation;

class ConversationDTO extends BaseDTO
{
    public int $id;
    public int $booking_id;
    public array $participants;
    public string $created_at;

    public function __construct(
        int $id,
        int $booking_id,
        array $participants,
        string $created_at
    ) {
        $this->id = $id;
        $this->booking_id = $booking_id;
        $this->participants = $participants;
        $this->created_at = $created_at;
    }

    /**
     * Create DTO from Conversation model
     */
    public static function fromModel(Conversation $conversation): self
    {
        // Map participants to array with user details
        $participants = $conversation->participants->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'avatar' => $user->avatar ?? null,
            ];
        })->values()->all();

        return new self(
            id: $conversation->id,
            booking_id: $conversation->booking_id,
            participants: $participants,
            created_at: $conversation->created_at?->format('Y-m-d\TH:i:s') ?? ''
        );
    }

    /**
     * Convert DTO to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'participants' => $this->participants,
            'created_at' => $this->created_at,
        ];
    }
}
