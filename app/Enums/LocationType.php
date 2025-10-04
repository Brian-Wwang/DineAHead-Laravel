<?php

namespace App\Enums;

enum LocationType:int
{
  case Indoor = 10;
  case Outdoor = 20;
  case Private = 30;

  public static function toArray(): array {
    return [
      self::Indoor->value => 'indoor',
      self::Outdoor->value => 'outdoor',
      self::Private->value => 'private',
    ];
  }

  public function label(): string {
        return match($this) {
            self::Indoor => 'Indoor',
            self::Outdoor => 'Outdoor',
            self::Private => 'Private',
        };
    }
}
