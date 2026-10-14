<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportCategory: string implements HasLabel
{
    case RapeApologia = 'rape_apologia';
    case VictimBlaming = 'victim_blaming';
    case SexualThreat = 'sexual_threat';
    case SexualHarassment = 'sexual_harassment';
    case Incitement = 'incitement';
    case Doxxing = 'doxxing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::RapeApologia => 'Rape apologia',
            self::VictimBlaming => 'Victim blaming',
            self::SexualThreat => 'Sexual threat',
            self::SexualHarassment => 'Sexual harassment',
            self::Incitement => 'Incitement to violence',
            self::Doxxing => 'Doxxing',
            self::Other => 'Other',
        };
    }
}
