<?php

namespace App\Enums;

enum PaymentStatus: int
{
    case Unpaid   = 10;
    case Paid     = 20;
    case Refunded = 30;

    public static function toArray(): array
    {
        return [
            self::Unpaid->value   => 'unpaid',
            self::Paid->value     => 'paid',
            self::Refunded->value => 'refunded',
        ];
    }
}
