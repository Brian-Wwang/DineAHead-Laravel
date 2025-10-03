<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformOptionValue extends Model
{
    protected $fillable = [
      'platform_option_id', 'name', 'extra_price', 'sort_order', 'is_active'
    ];

    public function option() { return $this->belongsTo(PlatformOption::class, 'platform_option_id'); }
}
