<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\LocationType;

class Table extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'images',
        'is_active',
        'remark',
        'seat_level_id',
        'location_type',
    ];

    protected $casts = [
        'images'        => 'array',
        'is_active'     => 'boolean',
        'location_type' => LocationType::class,
    ];

    // ✅ 删除 seat-level，正确隐藏 seatLevel 关系
    protected $hidden  = ['seat_level_id', 'seatLevel', 'store_id', 'location_type'];

    // ✅ 同时追加 seat_level_name 和 location_type_name
    protected $appends = ['seat_level_name', 'location_type_name'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function seatLevel()
    {
        return $this->belongsTo(SeatLevel::class, 'seat_level_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // seat_level_name
    public function getSeatLevelNameAttribute()
    {
        return $this->seatLevel->name ?? null;
    }

    // location_type_name
    public function getLocationTypeNameAttribute()
    {
        return $this->location_type?->name ?? null;
        // 或者用 label() 返回中文，比如 Indoor → 室内
    }
}
