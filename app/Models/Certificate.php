<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'consultant_id',
        'status',
        'rejected_reason',
        'is_verified',
        'verified_at',
        'document_scan_copy',
        'document_scan_copy_original_name',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Specify which file attributes should be stored privately (local storage).
     * The BaseRepository will use this to decide between public vs private storage.
     */
    public array $privateFiles = [
        'document_scan_copy',
    ];

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }
}
