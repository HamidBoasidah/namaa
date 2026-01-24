<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'consultant_id',
        'consultant_service_id',
        'client_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'consultant_id' => 'integer',
        'consultant_service_id' => 'integer',
        'client_id' => 'integer',
        'rating' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    /**
     * The booking this review is for
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * The consultant being reviewed
     */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class, 'consultant_id');
    }

    /**
     * The client who wrote the review
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * The consultant service being reviewed (if applicable)
     */
    public function consultantService(): BelongsTo
    {
        return $this->belongsTo(ConsultantService::class, 'consultant_service_id');
    }

    // ─────────────────────────────────────────────────────────────
    // Boot Method
    // ─────────────────────────────────────────────────────────────

    /**
     * Boot method to set consultant_service_id from booking
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            // Only set consultant_service_id if not already set
            if (!$review->consultant_service_id && $review->booking) {
                $bookable = $review->booking->bookable;
                
                // If bookable is ConsultantService, set the consultant_service_id
                if ($bookable instanceof ConsultantService) {
                    $review->consultant_service_id = $bookable->id;
                }
                // If bookable is Consultant directly, leave consultant_service_id as null
            }
        });
    }
}
