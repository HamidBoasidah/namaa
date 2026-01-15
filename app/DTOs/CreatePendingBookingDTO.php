<?php

namespace App\DTOs;

class CreatePendingBookingDTO extends BaseDTO
{
    public int $client_id;
    public int $consultant_id;
    public string $bookable_type;
    public int $bookable_id;
    public string $start_at;
    public ?int $duration_minutes;
    public ?string $notes;

    public function __construct(
        int $client_id,
        int $consultant_id,
        string $bookable_type,
        int $bookable_id,
        string $start_at,
        ?int $duration_minutes = null,
        ?string $notes = null
    ) {
        $this->client_id = $client_id;
        $this->consultant_id = $consultant_id;
        $this->bookable_type = $bookable_type;
        $this->bookable_id = $bookable_id;
        $this->start_at = $start_at;
        $this->duration_minutes = $duration_minutes;
        $this->notes = $notes;
    }

    /**
     * Create DTO from validated request data
     */
    public static function fromRequest(array $data, int $clientId): self
    {
        return new self(
            client_id: $clientId,
            consultant_id: (int) $data['consultant_id'],
            bookable_type: $data['bookable_type'],
            bookable_id: (int) $data['bookable_id'],
            start_at: $data['start_at'],
            duration_minutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            notes: $data['notes'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'client_id' => $this->client_id,
            'consultant_id' => $this->consultant_id,
            'bookable_type' => $this->bookable_type,
            'bookable_id' => $this->bookable_id,
            'start_at' => $this->start_at,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
        ];
    }
}
