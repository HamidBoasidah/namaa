<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingSection extends Model
{
    protected $fillable = [
        'landing_page_id',
        'type',
        'title',
        'subtitle',
        'description',
        'content',
        'settings',
        'background_color',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array',
        'settings' => 'array',
    ];

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LandingSectionItem::class)->orderBy('order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }
}
