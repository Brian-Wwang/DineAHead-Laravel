<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PaymentStatus;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'store_id',
        'reservation_id',
        'transaction_id',
        'total_price',
        'remark',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'payment_status' => PaymentStatus::class,
    ];

    /** 关系 */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
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
