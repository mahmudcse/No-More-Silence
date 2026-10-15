<?php

declare(strict_types=1);

namespace App\Support;

final class BanglaNumber
{
    private const DIGITS = [
        '0' => '০',
        '1' => '১',
        '2' => '২',
        '3' => '৩',
        '4' => '৪',
        '5' => '৫',
        '6' => '৬',
        '7' => '৭',
        '8' => '৮',
        '9' => '৯',
    ];

    public static function convert(string|int|float $value): string
    {
        return strtr((string) $value, self::DIGITS);
    }
}
