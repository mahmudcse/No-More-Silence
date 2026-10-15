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
        return $this->labelEn();
    }

    public function labelEn(): string
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

    public function labelBn(): string
    {
        return match ($this) {
            self::RapeApologia => 'ধর্ষণের সাফাই / ন্যায্যতা',
            self::VictimBlaming => 'ভিকটিম-ব্লেমিং',
            self::SexualThreat => 'যৌন সহিংসতার হুমকি',
            self::SexualHarassment => 'যৌন হয়রানি',
            self::Incitement => 'সহিংসতায় প্ররোচনা',
            self::Doxxing => 'ডক্সিং ও তথ্য প্রকাশ',
            self::Other => 'অন্যান্য',
        };
    }
}
