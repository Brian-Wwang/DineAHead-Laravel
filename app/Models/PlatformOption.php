<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformOption extends Model
{
  protected $fillable = [
    'name',
    'type',
    'is_active',
    'created_by',
    'created_by_name',
    'updated_by',
    'updated_by_name',
  ];

  public function values() { return $this->hasMany(PlatformOptionValue::class, 'platform_option_id'); }
}
