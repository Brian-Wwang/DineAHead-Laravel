<?php

namespace App\Enums;

enum ReservationStatus: int
{
  case Pending = 10;
  case Accepted = 20;
  case Confirmed = 30;
  case Completed = 40;
  case Cancelled = 50;

  public static function toArray(): array
  {
    return [
      self::Pending->value   => 'pending',
      self::Accepted->value    => 'accepted',
      self::Confirmed->value   => 'confirmed',
      self::Completed->value   => 'completed',
      self::Cancelled->value   => 'cancelled',
    ];
  }
}
