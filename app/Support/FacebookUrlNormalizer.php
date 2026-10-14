<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

final class FacebookUrlNormalizer
{
    private const HOSTS = [
        'facebook.com',
        'www.facebook.com',
        'm.facebook.com',
        'mbasic.facebook.com',
        'web.facebook.com',
        'fb.com',
        'www.fb.com',
    ];

    private const RESERVED_PATHS = [
        'about',
        'events',
        'friends',
        'fundraisers',
        'gaming',
        'groups',
        'hashtag',
        'help',
        'live',
        'marketplace',
        'messages',
        'notes',
        'notifications',
        'pages',
        'people',
        'permalink.php',
        'photo.php',
        'photos',
        'posts',
        'profile.php',
        'reel',
        'settings',
        'share',
        'stories',
        'videos',
        'watch',
    ];

    public function normalize(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (! Str::contains($url, '://')) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');

        if (! in_array($host, self::HOSTS, true)) {
            return null;
        }

        $segments = array_values(array_filter(
            explode('/', trim($parts['path'] ?? '', '/')),
            fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === []) {
            return null;
        }

        $first = strtolower($segments[0]);

        if ($first === 'profile.php') {
            return $this->canonicalFromNumericId($this->numericIdFromQuery($parts['query'] ?? ''));
        }

        if ($first === 'people') {
            return $this->canonicalFromNumericId($this->numericId($segments[count($segments) - 1] ?? ''));
        }

        if (count($segments) === 1 && ! ctype_digit($first) && ! in_array($first, self::RESERVED_PATHS, true)) {
            return "https://www.facebook.com/{$first}";
        }

        return null;
    }

    private function canonicalFromNumericId(?string $id): ?string
    {
        return $id !== null ? "https://www.facebook.com/profile.php?id={$id}" : null;
    }

    private function numericIdFromQuery(string $query): ?string
    {
        parse_str($query, $params);

        return $this->numericId($params['id'] ?? '');
    }

    private function numericId(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = (string) $value;

        return ($value !== '' && ctype_digit($value)) ? $value : null;
    }
}
