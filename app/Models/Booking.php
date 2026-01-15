<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends BaseModel
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    // Statuses that block time slots
    const BLOCKING_STATUSES = [self::STATUS_CONFIRMED, self::STATUS_PENDING];

    // Pending hold duration in minutes
    const PENDING_HOLD_MINUTES = 15;

    protected $fillable = [
        'client_id',
        'consultant_id',
        'bookable_type',
        'bookable_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'buffer_after_minutes',
        'status',
        'expires_at',
        'cancelled_at',
        'cancel_reason',
        'cancelled_by_type',
        'cancelled_by_id',
        'notes',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'consultant_id' => 'integer',
        'bookable_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'buffer_after_minutes' => 'integer',
    ];

    // Don't log these fields in activity log
    protected array $dontLog = ['expires_at'];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    /**
     * The client (user) who made the booking
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * The consultant being booked
     */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }

    /**
     * The bookable entity (Consultant or ConsultantService)
     */
    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Who cancelled the booking (User or Admin)
     */
    public function cancelledBy(): MorphTo
    {
        return $this->morphTo('cancelled_by');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    /**
     * Scope to get blocking bookings (confirmed OR pending with valid expiry)
     * These bookings block time slots for conflict checks
     */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', self::STATUS_CONFIRMED)
              ->orWhere(function (Builder $q2) {
                  $q2->where('status', self::STATUS_PENDING)
                     ->where('expires_at', '>', now());
              });
        });
    }

    /**
     * Scope to filter by consultant
     */
    public function scopeForConsultant(Builder $query, int $consultantId): Builder
    {
        return $query->where('consultant_id', $consultantId);
    }

    /**
     * Scope to filter by client
     */
    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope to find bookings that overlap with a given time range
     * Considers buffer_after_minutes in the occupied window
     * 
     * Overlap condition: new_start < existing_end + buffer AND new_end + buffer > existing_start
     */
    public function scopeOverlapping(Builder $query, Carbon $start, Carbon $end, int $bufferMinutes = 0): Builder
    {
        $newOccupiedEnd = $end->copy()->addMinutes($bufferMinutes);

        return $query->where(function (Builder $q) use ($start, $newOccupiedEnd) {
            // Existing occupied window: [start_at, end_at + buffer_after_minutes]
            // New occupied window: [start, end + bufferMinutes]
            // Overlap: new_start < existing_occupied_end AND new_occupied_end > existing_start
            $q->whereRaw('? < DATE_ADD(end_at, INTERVAL buffer_after_minutes MINUTE)', [$start])
              ->whereRaw('? > start_at', [$newOccupiedEnd]);
        });
    }

    /**
     * Scope to get pending bookings that have expired
     */
    public function scopeExpiredPending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('expires_at', '<=', now());
    }

    /**
     * Scope to get bookings on a specific date
     */
    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('start_at', $date->toDateString());
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if this booking is currently blocking
     */
    public function isBlocking(): bool
    {
        if ($this->status === self::STATUS_CONFIRMED) {
            return true;
        }

        if ($this->status === self::STATUS_PENDING && $this->expires_at && $this->expires_at->gt(now())) {
            return true;
        }

        return false;
    }

    /**
     * Get the occupied end time (end_at + buffer)
     */
    public function getOccupiedEndAttribute(): Carbon
    {
        return $this->end_at->copy()->addMinutes($this->buffer_after_minutes ?? 0);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if booking can be confirmed
     */
    public function canBeConfirmed(): bool
    {
        return $this->status === self::STATUS_PENDING 
            && $this->expires_at 
            && $this->expires_at->gt(now());
    }
}
