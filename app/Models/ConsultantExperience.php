<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantExperience extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consultant_id',
        'name',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }
}
