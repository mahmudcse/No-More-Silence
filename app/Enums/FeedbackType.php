<?php

declare(strict_types=1);

namespace App\Enums;

enum FeedbackType: string implements HasLabel
{
    case BugReport = 'bug_report';
    case FeatureRequest = 'feature_request';
    case ImprovementSuggestion = 'improvement_suggestion';
    case General = 'general';

    public function label(): string
    {
        return $this->labelEn();
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::BugReport => 'Bug report',
            self::FeatureRequest => 'Feature request',
            self::ImprovementSuggestion => 'Feature improvement',
            self::General => 'General feedback',
        };
    }

    public function labelBn(): string
    {
        return match ($this) {
            self::BugReport => 'বাগ বা ত্রুটি রিপোর্ট',
            self::FeatureRequest => 'নতুন ফিচারের অনুরোধ',
            self::ImprovementSuggestion => 'ফিচার উন্নয়নের পরামর্শ',
            self::General => 'সাধারণ মতামত',
        };
    }
}
