<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ReservationStatus;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'table_id',
        'user_id',
        'time_start',
        'time_end',
        'status',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
    ];

    protected $casts = [
        'status' => ReservationStatus::class,
        'time_start' => 'datetime',
        'time_end'   => 'datetime',
    ];

    /** 关系 */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function slots()
    {
        return $this->hasMany(ReservationSlot::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
