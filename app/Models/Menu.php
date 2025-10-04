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
    'name',
    'description',
    'price',
    'image',
    'discount_type',
    'discount_amount',
    'is_active',
  ];

  protected $casts = [
    'price'           => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'discount_type'   => 'integer',
    'is_active'       => 'boolean',
  ];

  public function categories() {
    return $this->belongsToMany(Category::class, 'menu_category')
                ->withTimestamps()
                ->withPivot('deleted_at'); // 软删除中间表;
  }
  public function options() { return $this->hasMany(MenuOption::class); }
  // private function applyDiscount($price, $type, $value): float {
  //   return match($type) {
  //     DiscountType::Percentage->value => $price * (1 - $value / 100),
  //     DiscountType::Actual->value     => max(0, $price - $value),
  //     DiscountType::Fix->value        => $value,
  //     default                         => $price,
  //   };
  // }
  public function orderItems() { return $this->hasMany(OrderItem::class); }
}
