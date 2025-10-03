<?php

namespace App\Enums;

enum DiscountType: int
{
    case None       = 0;
    case Percentage = 10;
    case Actual     = 20;
    case Fix        = 30;

    public static function toArray(): array
    {
        return [
            self::None->value       => 'none',
            self::Percentage->value => 'percentage',
            self::Actual->value     => 'actual',
            self::Fix->value        => 'fix'
        ];
    }
}
