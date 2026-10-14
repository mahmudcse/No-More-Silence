<?php

declare(strict_types=1);

namespace App\Enums;

enum AliasType: string implements HasLabel
{
    case Url = 'url';
    case Name = 'name';

    public function label(): string
    {
        return match ($this) {
            self::Url => 'URL',
            self::Name => 'Name',
        };
    }
}
