<?php

declare(strict_types=1);

use App\Support\BanglaNumber;

it('converts western digits to bangla digits', function (): void {
    $cases = [
        '0' => '০',
        '1' => '১',
        '2' => '২',
        '3' => '৩',
        '4' => '৪',
        '5' => '৫',
        '6' => '৬',
        '7' => '৭',
        '8' => '৮',
        '9' => '৯',
        '123' => '১২৩',
        '2024' => '২০২৪',
        '109' => '১০৯',
    ];

    foreach ($cases as $input => $expected) {
        expect(BanglaNumber::convert($input))->toBe($expected);
    }
});

it('leaves non-digit characters untouched', function (): void {
    expect(BanglaNumber::convert('Phone: 01712-345678'))->toBe('Phone: ০১৭১২-৩৪৫৬৭৮');
});
