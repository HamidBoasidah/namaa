<?php

namespace App\DTOs;

use App\Models\Booking;

class BookingDTO extends BaseDTO
{
    public int $id;
    public int $client_id;
    public ?string $client_name;
    public ?string $client_avatar;
    public ?string $client_email;
    public ?string $client_phone;
    public int $consultant_id;
    public ?string $consultant_name;
    public ?string $consultant_avatar;
    public ?string $consultant_email;
    public ?string $consultant_phone;
    public string $bookable_type;
    public string $bookable_type_short;
    public int $bookable_id;
    public ?string $bookable_title;
    public ?string $consultation_method;
    public string $start_at;
    public string $end_at;
    public int $duration_minutes;
    public int $buffer_after_minutes;
    public string $status;
    public ?string $expires_at;
    public ?string $cancelled_at;
    public ?string $cancel_reason;
    public ?string $cancelled_by_type;
    public ?int $cancelled_by_id;
    public ?string $cancelled_by_name;
    public ?string $notes;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(
        int $id,
        int $client_id,
        ?string $client_name,
        ?string $client_avatar,
        ?string $client_email,
        ?string $client_phone,
        int $consultant_id,
        ?string $consultant_name,
        ?string $consultant_avatar,
        ?string $consultant_email,
        ?string $consultant_phone,
        string $bookable_type,
        string $bookable_type_short,
        int $bookable_id,
        ?string $bookable_title,
        ?string $consultation_method,
        string $start_at,
        string $end_at,
        int $duration_minutes,
        int $buffer_after_minutes,
        string $status,
        ?string $expires_at,
        ?string $cancelled_at,
        ?string $cancel_reason,
        ?string $cancelled_by_type,
        ?int $cancelled_by_id,
        ?string $cancelled_by_name,
        ?string $notes,
        ?string $created_at,
        ?string $updated_at
    ) {
        $this->id = $id;
        $this->client_id = $client_id;
        $this->client_name = $client_name;
        $this->client_avatar = $client_avatar;
    $this->client_email = $client_email;
    $this->client_phone = $client_phone;
        $this->consultant_id = $consultant_id;
        $this->consultant_name = $consultant_name;
        $this->consultant_avatar = $consultant_avatar;
    $this->consultant_email = $consultant_email;
    $this->consultant_phone = $consultant_phone;
        $this->bookable_type = $bookable_type;
        $this->bookable_type_short = $bookable_type_short;
        $this->bookable_id = $bookable_id;
        $this->bookable_title = $bookable_title;
        $this->consultation_method = $consultation_method;
        $this->start_at = $start_at;
        $this->end_at = $end_at;
        $this->duration_minutes = $duration_minutes;
        $this->buffer_after_minutes = $buffer_after_minutes;
        $this->status = $status;
        $this->expires_at = $expires_at;
        $this->cancelled_at = $cancelled_at;
        $this->cancel_reason = $cancel_reason;
        $this->cancelled_by_type = $cancelled_by_type;
        $this->cancelled_by_id = $cancelled_by_id;
        $this->cancelled_by_name = $cancelled_by_name;
        $this->notes = $notes;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Create DTO from Booking model
     */
    public static function fromModel(Booking $booking): self
    {
        // Get bookable title based on type
        $bookableTitle = null;
        if ($booking->bookable) {
            if ($booking->bookable_type === \App\Models\ConsultantService::class) {
                $bookableTitle = $booking->bookable->title ?? null;
            } else {
                $bookableTitle = $booking->consultant?->user?->name ?? null;
            }
        }

        // Get consultation method from booking (stored at booking creation)
        $consultationMethod = $booking->consultation_method;

        // Get cancelled by name
        $cancelledByName = null;
        if ($booking->cancelledBy) {
            $cancelledByName = $booking->cancelledBy->name ?? null;
        }

        // derive a short key used for translations (matches bookings.bookableTypes keys)
        $shortType = class_basename($booking->bookable_type) === 'Consultant' ? 'consultant' : (class_basename($booking->bookable_type) === 'ConsultantService' ? 'consultantService' : strtolower(class_basename($booking->bookable_type)));

        return new self(
            id: $booking->id,
            client_id: $booking->client_id,
            client_name: $booking->client?->name ?? null,
            client_avatar: $booking->client?->avatar ?? null,
            client_email: $booking->client?->email ?? null,
            client_phone: $booking->client?->phone_number ?? null,
            consultant_id: $booking->consultant_id,
            consultant_name: $booking->consultant?->user?->name ?? null,
            consultant_avatar: $booking->consultant?->user?->avatar ?? null,
            consultant_email: $booking->consultant?->user?->email ?? null,
            consultant_phone: $booking->consultant?->user?->phone_number ?? null,
            bookable_type: class_basename($booking->bookable_type),
            bookable_type_short: $shortType,
            bookable_id: $booking->bookable_id,
            bookable_title: $bookableTitle,
            consultation_method: $consultationMethod,
            start_at: $booking->start_at?->format('Y-m-d\TH:i:s') ?? '',
            end_at: $booking->end_at?->format('Y-m-d\TH:i:s') ?? '',
            duration_minutes: $booking->duration_minutes,
            buffer_after_minutes: $booking->buffer_after_minutes ?? 0,
            status: $booking->status,
            expires_at: $booking->expires_at?->format('Y-m-d\TH:i:s'),
            cancelled_at: $booking->cancelled_at?->format('Y-m-d\TH:i:s'),
            cancel_reason: $booking->cancel_reason,
            cancelled_by_type: $booking->cancelled_by_type ? class_basename($booking->cancelled_by_type) : null,
            cancelled_by_id: $booking->cancelled_by_id,
            cancelled_by_name: $cancelledByName,
            notes: $booking->notes,
            created_at: $booking->created_at?->format('Y-m-d\TH:i:s'),
            updated_at: $booking->updated_at?->format('Y-m-d\TH:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client_name' => $this->client_name,
            'client_avatar' => $this->client_avatar,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'consultant_id' => $this->consultant_id,
            'consultant_name' => $this->consultant_name,
            'consultant_avatar' => $this->consultant_avatar,
            'consultant_email' => $this->consultant_email,
            'consultant_phone' => $this->consultant_phone,
            'bookable_type' => $this->bookable_type,
            'bookable_type_short' => $this->bookable_type_short,
            'bookable_id' => $this->bookable_id,
            'bookable_title' => $this->bookable_title,
            'consultation_method' => $this->consultation_method,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'duration_minutes' => $this->duration_minutes,
            'buffer_after_minutes' => $this->buffer_after_minutes,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'cancelled_at' => $this->cancelled_at,
            'cancel_reason' => $this->cancel_reason,
            'cancelled_by_type' => $this->cancelled_by_type,
            'cancelled_by_id' => $this->cancelled_by_id,
            'cancelled_by_name' => $this->cancelled_by_name,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Return a simplified array for list views
     */
    public function toIndexArray(): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'consultant_name' => $this->consultant_name,
            'bookable_type' => $this->bookable_type,
            'bookable_title' => $this->bookable_title,
            'consultation_method' => $this->consultation_method,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
        ];
    }
}
