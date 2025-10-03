<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\CategoryType;
use App\Enums\DiscountType;

class Category extends Model
{
  protected $fillable = [
    'store_id',
    'name',
    'type',
    'discount_type',
    'discount_value',
    'is_active',
    'created_by',
    'created_by_name',
    'updated_by',
    'updated_by_name',
  ];

  /*
  |--------------------------------------------------------------------------
  | Relationships
  |--------------------------------------------------------------------------
  */

  // 一个分类属于一个店铺（可能是 null）
  public function store()
  {
      return $this->belongsTo(Store::class);
  }

  // 一个分类可以绑定多个菜单（多对多）
  public function menus()
  {
      return $this->belongsToMany(Menu::class, 'menu_category')
                  ->withTimestamps()
                  ->withPivot('deleted_at'); // 软删除中间表;
  }

  /*
  |--------------------------------------------------------------------------
  | Helpers
  |--------------------------------------------------------------------------
  */

  // 判断是否是优惠分类
  public function isDiscountCategory(): bool
  {
      return $this->type === CategoryType::Discount->value;
  }

  // 判断是否是平台分类
  public function isPlatformCategory(): bool
  {
      return is_null($this->store_id);
  }

  // 判断是否是店铺分类
  public function isStoreCategory(): bool
  {
      return !is_null($this->store_id);
  }

  // 计算分类优惠后的价格（给 Menu 用）
  public function applyDiscount(float $price): float
  {
      return match ($this->discount_type) {
          DiscountType::Percentage->value => round($price * (1 - $this->discount_value / 100), 2),
          DiscountType::Actual->value     => max($price - $this->discount_value, 0),
          DiscountType::Fix->value        => (float) $this->discount_value,
          default                         => $price,
      };
  }
}
