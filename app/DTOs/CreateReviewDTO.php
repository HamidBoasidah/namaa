<?php

namespace App\DTOs;

class CreateReviewDTO extends BaseDTO
{
    public int $booking_id;
    public int $rating;
    public ?string $comment;
    public int $client_id;

    public function __construct(
        int $booking_id,
        int $rating,
        ?string $comment,
        int $client_id
    ) {
        $this->booking_id = $booking_id;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->client_id = $client_id;
    }

    /**
     * Create DTO from validated request data
     */
    public static function fromRequest(array $validated, int $clientId): self
    {
        return new self(
            booking_id: (int) $validated['booking_id'],
            rating: (int) $validated['rating'],
            comment: $validated['comment'] ?? null,
            client_id: $clientId
        );
    }

    public function toArray(): array
    {
        return [
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'client_id' => $this->client_id,
        ];
    }
}
