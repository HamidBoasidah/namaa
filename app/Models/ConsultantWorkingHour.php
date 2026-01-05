<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantWorkingHour extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'consultant_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'consultant_id' => 'integer',
        'day_of_week'   => 'integer',
        'start_time'    => 'string',   // time stored as string "HH:MM"
        'end_time'      => 'string',
        'is_active'     => 'boolean',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }
}