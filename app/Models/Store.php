<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
  protected $fillable = [
    'name','contact','email','description','address','cover',
    'time_start','time_close', 'price_level_id', 'latitute', 'longitude',
    'province_id','city_id', 'user_id'
  ];

  protected $casts = [
    'time_start' => 'datetime:H:i',
    'time_close' => 'datetime:H:i',
  ];

  // 不要写 'time' cast；如果想统一输出成 HH:MM，可自定义取值器：
  public function getTimeStartAttribute($v) { return substr($v, 0, 5); }
  public function getTimeCloseAttribute($v) { return substr($v, 0, 5); }
  public function user() { return $this->belongsTo(User::class); }public function province() {
    return $this->belongsTo(Location::class, 'province_id', 'code')
                ->where('type', 'province');
  }
  public function city() {
    return $this->belongsTo(Location::class, 'city_id', 'code')
                ->where('type', 'district');
  }
  public function cuisines() { return $this->belongsToMany(Cuisine::class, 'relate_cuisine_store'); }
  public function priceLevel() { return $this->belongsTo(PriceLevel::class, 'price_level_id'); }
  public function tables() { return $this->hasMany(Table::class); }
  public function menus() { return $this->hasMany(Menu::class); }
  public function orders() { return $this->hasMany(Order::class); }
  public function reservations() { return $this->hasMany(Reservation::class); }
}
