<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PreUser extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'email', 'verify_code', 'expired_at', 'type'
  ];

  protected $casts = [
    'expired_at' => 'datetime',
  ];
}
