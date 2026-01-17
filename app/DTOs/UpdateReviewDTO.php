<?php

namespace App\DTOs;

class UpdateReviewDTO extends BaseDTO
{
    public int $rating;
    public ?string $comment;

    public function __construct(
        int $rating,
        ?string $comment
    ) {
        $this->rating = $rating;
        $this->comment = $comment;
    }

    /**
     * Create DTO from validated request data
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            rating: (int) $validated['rating'],
            comment: $validated['comment'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'rating' => $this->rating,
            'comment' => $this->comment,
        ];
    }
}
