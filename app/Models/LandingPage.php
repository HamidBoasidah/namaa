<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'meta_title',
        'meta_description',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(LandingSection::class)->orderBy('order');
    }

    public function activeSections(): HasMany
    {
        return $this->sections()->where('is_active', true);
    }
}
