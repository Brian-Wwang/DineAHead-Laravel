<?php

namespace App\Enums;

enum CategoryType: int
{
  case Normal = 10;
  case Discount = 20;

  public static function toArray(): array
  {
    return [
      self::Normal->value => 'normal',
      self::Discount->value => 'discount'
    ];
  }
}

