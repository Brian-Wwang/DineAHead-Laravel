<?php

namespace App\Models;

use App\Enums\MenuStatus;
use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'discount_type',
        'discount_amount',
        'status',
        'is_active',
        'current_booking_id',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'status'          => 'integer',
        'discount_type'   => 'integer',
        'is_active'            => 'boolean',
    ];

    public function getStatusLabelAttribute(): string
    {
        return MenuStatus::toArray()[$this->status] ?? 'unknown';
    }

    public function getDiscountTypeLabelAttribute(): string
    {
        return DiscountType::toArray()[$this->discount_type] ?? 'none';
    }
}
