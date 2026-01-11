<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'consultation_type_id',
        'name',
        'slug',
        'is_active',
        'icon_path',
        'created_by',
        'updated_by',
    ];


    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_type_id');
    }

    /**
     * علاقة الفئة بخدمات المستشارين
     */
    public function consultantServices()
    {
        return $this->hasMany(ConsultantService::class, 'category_id');
    }

    /**
     * Get the icon URL attribute.
     */
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? Storage::url($this->icon_path) : null;
    }
}
