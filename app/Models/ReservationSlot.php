<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationSlot extends Model
{
    protected $fillable = [
        'reservation_id',
        'slot_start',
        'slot_end',
        'is_active',
    ];

    protected $casts = [
        'slot_start' => 'datetime',
        'slot_end'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    /** 关系 */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
