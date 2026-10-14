<?php

declare(strict_types=1);

namespace App\Enums;

enum Platform: string implements HasLabel
{
    case Facebook = 'facebook';
    case Youtube = 'youtube';
    case Tiktok = 'tiktok';

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Youtube => 'YouTube',
            self::Tiktok => 'TikTok',
        };
    }
}
