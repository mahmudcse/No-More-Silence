<?php

declare(strict_types=1);

namespace App\Enums;

enum SubmittedVia: string implements HasLabel
{
    case Web = 'web';
    case Pwa = 'pwa';
    case Android = 'android';
    case Api = 'api';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Pwa => 'PWA',
            self::Android => 'Android',
            self::Api => 'API',
            self::Admin => 'Admin',
        };
    }
}
