<?php

namespace App\DTOs;

use App\Models\Review;

class ReviewDTO extends BaseDTO
{
    public int $id;
    public string $client_name;
    public ?string $client_avatar;
    public int $rating;
    public ?string $comment;
    public string $created_at;

    public function __construct(
        int $id,
        string $client_name,
        ?string $client_avatar,
        int $rating,
        ?string $comment,
        string $created_at
    ) {
        $this->id = $id;
        $this->client_name = $client_name;
        $this->client_avatar = $client_avatar;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->created_at = $created_at;
    }

    /**
     * Create DTO from Review model
     */
    public static function fromModel(Review $review): self
    {
        // Build client name from first_name and last_name
        $clientName = trim(
            ($review->client?->first_name ?? '') . ' ' . ($review->client?->last_name ?? '')
        );
        
        return new self(
            id: $review->id,
            client_name: $clientName,
            client_avatar: $review->client?->avatar ?? null,
            rating: $review->rating,
            comment: $review->comment,
            created_at: $review->created_at?->format('Y-m-d\TH:i:s') ?? ''
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'client_avatar' => $this->client_avatar,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
        ];
    }
}
