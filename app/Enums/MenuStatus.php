<?php

namespace App\Enums;

enum MenuStatus: int
{
    case Available = 0;
    case Pending   = 1;
    case Accept    = 2;
    case Confirm   = 3;

    public static function toArray(): array
    {
        return [
            self::Available->value => 'available',
            self::Pending->value   => 'pending',
            self::Accept->value    => 'accept',
            self::Confirm->value   => 'confirm',
        ];
    }
}
