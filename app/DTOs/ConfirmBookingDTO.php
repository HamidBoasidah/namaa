<?php

namespace App\DTOs;

class ConfirmBookingDTO extends BaseDTO
{
    public int $booking_id;
    public int $client_id;

    public function __construct(
        int $booking_id,
        int $client_id
    ) {
        $this->booking_id = $booking_id;
        $this->client_id = $client_id;
    }

    /**
     * Create DTO from request parameters
     */
    public static function fromRequest(int $bookingId, int $clientId): self
    {
        return new self(
            booking_id: $bookingId,
            client_id: $clientId
        );
    }

    public function toArray(): array
    {
        return [
            'booking_id' => $this->booking_id,
            'client_id' => $this->client_id,
        ];
    }
}
