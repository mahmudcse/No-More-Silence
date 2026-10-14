<?php

declare(strict_types=1);

namespace App\Enums;

enum ProfileStatus: string implements HasLabel
{
    case Active = 'active';
    case Deactivated = 'deactivated';
    case Renamed = 'renamed';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Deactivated => 'Deactivated',
            self::Renamed => 'Renamed',
            self::Unknown => 'Unknown',
        };
    }
}
