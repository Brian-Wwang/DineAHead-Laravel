<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuisine extends Model
{
  use SoftDeletes;

  protected $fillable = [
      'name', 'description', 'is_active',
      'created_by', 'created_by_name',
      'updated_by', 'updated_by_name',
  ];

  public function stores()
  {
    return $this->belongsToMany(Store::class, 'relate_cuisine_store');
  }

}
