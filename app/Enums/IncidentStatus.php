<?php

declare(strict_types=1);

namespace App\Enums;

enum IncidentStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Public => 'Public',
        };
    }
}
