<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantHoliday extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultant_id',
        'holiday_date',
        'name',
    ];

    protected $casts = [
        'consultant_id' => 'integer',
        'holiday_date'  => 'date',
        'name'          => 'string',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }
}
