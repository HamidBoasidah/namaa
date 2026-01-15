<?php

namespace App\DTOs;

class GetAvailableSlotsDTO extends BaseDTO
{
    public int $consultant_id;
    public string $date;
    public ?string $bookable_type;
    public ?int $bookable_id;

    public function __construct(
        int $consultant_id,
        string $date,
        ?string $bookable_type = null,
        ?int $bookable_id = null
    ) {
        $this->consultant_id = $consultant_id;
        $this->date = $date;
        $this->bookable_type = $bookable_type;
        $this->bookable_id = $bookable_id;
    }

    /**
     * Create DTO from validated request data
     */
    public static function fromRequest(array $data, int $consultantId): self
    {
        return new self(
            consultant_id: $consultantId,
            date: $data['date'],
            bookable_type: $data['bookable_type'] ?? null,
            bookable_id: isset($data['bookable_id']) ? (int) $data['bookable_id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'consultant_id' => $this->consultant_id,
            'date' => $this->date,
            'bookable_type' => $this->bookable_type,
            'bookable_id' => $this->bookable_id,
        ];
    }
}
