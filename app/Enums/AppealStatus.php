<?php

declare(strict_types=1);

namespace App\Enums;

enum AppealStatus: string implements HasLabel
{
    case Open = 'open';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Partially = 'partially';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Partially => 'Partially accepted',
        };
    }
}
