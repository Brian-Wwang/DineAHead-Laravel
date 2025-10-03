<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOptionValue extends Model
{
    protected $fillable = ['store_option_id','name','extra_price','is_active','created_by','created_by_name','updated_by','updated_by_name'];

    public function option() { return $this->belongsTo(StoreOption::class, 'store_option_id'); }
}
