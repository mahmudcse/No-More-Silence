<?php

declare(strict_types=1);

namespace App\Enums;

enum FeedbackStatus: string implements HasLabel
{
    case Open = 'open';
    case UnderReview = 'under_review';
    case Planned = 'planned';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return $this->labelEn();
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::UnderReview => 'Under review',
            self::Planned => 'Planned',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function labelBn(): string
    {
        return match ($this) {
            self::Open => 'অপেক্ষমান (Open)',
            self::UnderReview => 'পর্যালোচনাধীন (Under review)',
            self::Planned => 'পরিকল্পনাভুক্ত (Planned)',
            self::Resolved => 'সমাধানকৃত (Resolved)',
            self::Closed => 'সম্পন্ন/বন্ধ (Closed)',
        };
    }
}
