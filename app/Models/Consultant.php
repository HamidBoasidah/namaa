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
        'consultation_type_id',
        'years_of_experience',
        'rating_avg',
        'ratings_count',
        'price_per_hour',
        'is_active',
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
        'rating_avg'          => 'decimal:2',
        'ratings_count'       => 'integer',
        'price_per_hour'      => 'decimal:2',
        'is_active'           => 'boolean',
        'deleted_at'          => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_type_id');
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

    public function holidays()
    {
        return $this->hasMany(ConsultantHoliday::class)
            ->orderBy('holiday_date');
    }

    public function service()
    {
        return $this->hasOne(ConsultantService::class);
    }

    public function experiences()
    {
        return $this->hasMany(ConsultantExperience::class)
            ->orderBy('name');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class)
            ->orderBy('created_at', 'desc');
    }
}
