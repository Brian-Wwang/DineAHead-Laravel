<?php

namespace App\Enums;

enum OptionType: int
{
  case Single = 10;
  case Multiple = 20;

  public static function toArray(): array
  {
    return [
      self::Single->value => 'single',
      self::Multiple->value => 'multiple'
    ];
  }
}
