<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOption extends Model
{

  protected $fillable = ['store_id','name','type','is_active','created_by','created_by_name','updated_by','updated_by_name'];

    public function values() { return $this->hasMany(StoreOptionValue::class, 'store_option_id'); }
    public function store() { return $this->belongsTo(Store::class); }
}
