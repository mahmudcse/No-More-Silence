<?php

declare(strict_types=1);

use App\Support\FacebookUrlNormalizer;

$normalizer = new FacebookUrlNormalizer;

it('maps profile.php id urls to one canonical key', function (string $input) use ($normalizer): void {
    expect($normalizer->normalize($input))->toBe('https://www.facebook.com/profile.php?id=1000123456789');
})->with([
    'desktop' => 'https://www.facebook.com/profile.php?id=1000123456789',
    'mobile' => 'https://m.facebook.com/profile.php?id=1000123456789',
    'mbasic' => 'https://mbasic.facebook.com/profile.php?id=1000123456789',
    'web' => 'https://web.facebook.com/profile.php?id=1000123456789',
    'fb shortcut' => 'https://fb.com/profile.php?id=1000123456789',
    'tracking params' => 'https://www.facebook.com/profile.php?id=1000123456789&mibextid=abc&ref=xyz',
    'trailing slash' => 'https://www.facebook.com/profile.php/?id=1000123456789',
    'no scheme' => 'www.facebook.com/profile.php?id=1000123456789',
    'uppercase host' => 'HTTPS://WWW.FACEBOOK.COM/profile.php?id=1000123456789',
]);

it('maps people urls to a canonical numeric id key', function (string $input) use ($normalizer): void {
    expect($normalizer->normalize($input))->toBe('https://www.facebook.com/profile.php?id=1000123456789');
})->with([
    'desktop' => 'https://www.facebook.com/people/John-Doe/1000123456789',
    'mobile' => 'https://m.facebook.com/people/John-Doe/1000123456789',
    'trailing slash' => 'https://www.facebook.com/people/John-Doe/1000123456789/',
]);

it('maps username urls to a canonical lowercased key', function (string $input) use ($normalizer): void {
    expect($normalizer->normalize($input))->toBe('https://www.facebook.com/johndoe');
})->with([
    'desktop' => 'https://www.facebook.com/johndoe',
    'mobile' => 'https://m.facebook.com/johndoe',
    'web' => 'https://web.facebook.com/johndoe',
    'fb shortcut' => 'https://fb.com/johndoe',
    'trailing slash' => 'https://www.facebook.com/johndoe/',
    'tracking params' => 'https://www.facebook.com/johndoe?mibextid=abc&ref=xyz',
    'no scheme' => 'www.facebook.com/johndoe',
    'uppercase username' => 'https://www.facebook.com/JohnDoe',
]);

it('returns null for non-profile or ambiguous urls', function (string $input) use ($normalizer): void {
    expect($normalizer->normalize($input))->toBeNull();
})->with([
    'group' => 'https://www.facebook.com/groups/12345',
    'photo' => 'https://www.facebook.com/photo.php?fbid=12345',
    'non-facebook host' => 'https://twitter.com/johndoe',
    'empty string' => '',
    'whitespace only' => '   ',
    'profile.php without id' => 'https://www.facebook.com/profile.php',
    'bare numeric id' => 'https://www.facebook.com/1000123456789',
    'people without numeric id' => 'https://www.facebook.com/people/John-Doe',
    'root path' => 'https://www.facebook.com/',
]);
