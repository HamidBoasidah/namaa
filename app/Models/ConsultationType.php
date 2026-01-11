<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultationType extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon_path',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the icon URL attribute.
     */
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? \Illuminate\Support\Facades\Storage::url($this->icon_path) : null;
    }

    public function consultants()
    {
        return $this->hasMany(Consultant::class, 'consultation_type_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'consultation_type_id');
    }
}

