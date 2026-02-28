<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingSectionItem extends Model
{
    protected $fillable = [
        'landing_section_id',
        'title',
        'subtitle',
        'description',
        'content',
        'image',
        'icon',
        'link',
        'link_text',
        'background_color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(LandingSection::class, 'landing_section_id');
    }
}
