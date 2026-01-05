<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelLang\Publisher\Console\Base;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultant extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'display_name',
        'bio',
        'email',
        'phone',
        'years_of_experience',
        'specialization_summary',
        'profile_image',
        'address',
        'governorate_id',
        'district_id',
        'area_id',
        'rating_avg',
        'ratings_count',
        'is_active',
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
        'rating_avg'          => 'decimal:2',
        'ratings_count'       => 'integer',
        'is_active'           => 'boolean',
        'deleted_at'          => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function workingHours()
    {
        return $this->hasMany(ConsultantWorkingHour::class);
    }

    public function activeWorkingHours()
    {
        return $this->hasMany(ConsultantWorkingHour::class)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time');
    }

}
