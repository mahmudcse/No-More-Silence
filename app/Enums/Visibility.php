<?php

declare(strict_types=1);

namespace App\Enums;

enum Visibility: string implements HasLabel
{
    case Hidden = 'hidden';
    case Public = 'public';
    case Featured = 'featured';

    public function label(): string
    {
        return match ($this) {
            self::Hidden => 'Hidden',
            self::Public => 'Public',
            self::Featured => 'Featured',
        };
    }
}
