<?php

declare(strict_types=1);

namespace App\Enums;

enum DistrictSource: string implements HasLabel
{
    case SelfDeclared = 'self_declared';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::SelfDeclared => 'Self-declared',
            self::Unknown => 'Unknown',
        };
    }
}
