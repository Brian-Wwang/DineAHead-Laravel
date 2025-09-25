<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
  protected $fillable = [
    'name','contact','email','description','address','cover',
    'time_start','time_close', 'price_level', 'latitute', 'longitute',
    'province_id','city_id', 'user_id'
  ];

  // 不要写 'time' cast；如果想统一输出成 HH:MM，可自定义取值器：
  public function getTimeStartAttribute($v) { return substr($v, 0, 5); }
  public function getTimeCloseAttribute($v) { return substr($v, 0, 5); }

  public function user() { return $this->belongsTo(User::class); }
  public function province() { return $this->belongsTo(Location::class, 'province_id', 'code')->where('type', 'province'); }
  public function city() { return $this->belongsTo(Location::class, 'city_id', 'code')->where('type', 'district'); }
  public function cuisines() { return $this->belongsToMany(Cuisine::class, 'relate_cuisine_store'); }
}
