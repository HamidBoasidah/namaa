<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kyc extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'rejected_reason',
        'is_verified',
        'verified_at',
        'full_name',
        'gender',
        'date_of_birth',
        'address',
        'document_type',
        'document_scan_copy',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
    ];

    /**
     * Specify which file attributes should be stored privately (local storage).
     * The BaseRepository will use this to decide between public vs private storage.
     */
    public array $privateFiles = [
        'document_scan_copy',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
