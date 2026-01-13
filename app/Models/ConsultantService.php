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
        'buffer',
        'duration_minutes',
        'consultation_method',
        'delivery_time',
        'auto_accept_requests',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'buffer' => 'integer',
        'auto_accept_requests' => 'boolean',
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

    // تفاصيل الخدمة
    public function details()
    {
        return $this->hasMany(ConsultantServiceDetail::class)->orderBy('sort_order');
    }

    // ماذا تشمل الخدمة
    public function includes()
    {
        return $this->hasMany(ConsultantServiceDetail::class)
            ->where('type', ConsultantServiceDetail::TYPE_INCLUDES)
            ->orderBy('sort_order');
    }

    // لمن هذه الخدمة
    public function targetAudience()
    {
        return $this->hasMany(ConsultantServiceDetail::class)
            ->where('type', ConsultantServiceDetail::TYPE_TARGET_AUDIENCE)
            ->orderBy('sort_order');
    }

    // ما الذي يستلمه العميل
    public function deliverables()
    {
        return $this->hasMany(ConsultantServiceDetail::class)
            ->where('type', ConsultantServiceDetail::TYPE_DELIVERABLES)
            ->orderBy('sort_order');
    }
}
