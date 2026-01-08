<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultationType extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'created_by',
        'updated_by',
    ];
}

