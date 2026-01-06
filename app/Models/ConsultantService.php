<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use App\Models\Tag;


class ConsultantService extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'consultant_id',
        'title',
        'description',
        'price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'consultant_services_tags')->withTimestamps();
    }
}
