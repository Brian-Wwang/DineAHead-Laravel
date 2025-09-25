<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'images',
        'is_active',
        'status',
        'current_booking_id',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active'   => 'boolean',
        'status' => 'integer',
    ];

    // 数字枚举常量
    const STATUS_AVAILABLE = 0;
    const STATUS_PENDING   = 1;
    const STATUS_ACCEPT    = 2;
    const STATUS_CONFIRM   = 3;

    public static array $statusLabels = [
        self::STATUS_AVAILABLE => 'available',
        self::STATUS_PENDING   => 'pending',
        self::STATUS_ACCEPT    => 'accept',
        self::STATUS_CONFIRM   => 'confirm',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? 'unknown';
    }

    // Scope：只查 is_active=true 的记录
    public function scopeVisible($query)
    {
        return $query->where('is_active', true);
    }
}
