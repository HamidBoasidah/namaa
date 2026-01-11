<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConsultantServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultant_service_id',
        'type',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // الأنواع المتاحة
    const TYPE_INCLUDES = 'includes';           // ماذا تشمل الخدمة
    const TYPE_TARGET_AUDIENCE = 'target_audience'; // لمن هذه الخدمة
    const TYPE_DELIVERABLES = 'deliverables';   // ما الذي يستلمه العميل

    public static function types(): array
    {
        return [
            self::TYPE_INCLUDES => 'ماذا تشمل الخدمة',
            self::TYPE_TARGET_AUDIENCE => 'لمن هذه الخدمة',
            self::TYPE_DELIVERABLES => 'ما الذي يستلمه العميل',
        ];
    }

    public function consultantService()
    {
        return $this->belongsTo(ConsultantService::class);
    }
}
