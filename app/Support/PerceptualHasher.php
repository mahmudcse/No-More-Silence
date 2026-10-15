<?php

declare(strict_types=1);

namespace App\Support;

use GdImage;
use InvalidArgumentException;

/**
 * Computes a 64-bit difference hash (dHash) of an image using GD.
 *
 * Visually identical screenshots (re-encoded, resized, recompressed) produce
 * hashes with a small Hamming distance, which lets moderators spot duplicate
 * or corroborating submissions of the same comment.
 */
final class PerceptualHasher
{
    private const WIDTH = 9;

    private const HEIGHT = 8;

    /**
     * Hashes the image at the given path and returns a 16-character hex string.
     */
    public function hash(string $path): string
    {
        $contents = @file_get_contents($path);
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;

        if ($source === false) {
            throw new InvalidArgumentException("Unable to read image at [{$path}].");
        }

        $thumbnail = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, self::WIDTH, self::HEIGHT, imagesx($source), imagesy($source));

        $bits = '';

        for ($y = 0; $y < self::HEIGHT; $y++) {
            for ($x = 0; $x < self::WIDTH - 1; $x++) {
                $bits .= $this->luminance($thumbnail, $x, $y) > $this->luminance($thumbnail, $x + 1, $y) ? '1' : '0';
            }
        }

        return implode('', array_map(
            fn (string $nibble): string => dechex((int) bindec($nibble)),
            str_split($bits, 4),
        ));
    }

    /**
     * Number of differing bits between two hashes (0 = identical, 64 = opposite).
     */
    public function distance(string $first, string $second): int
    {
        if (strlen($first) !== strlen($second)) {
            throw new InvalidArgumentException('Hashes must have the same length.');
        }

        $distance = 0;

        foreach (str_split($first) as $index => $character) {
            $distance += substr_count(decbin(hexdec($character) ^ hexdec($second[$index])), '1');
        }

        return $distance;
    }

    private function luminance(GdImage $image, int $x, int $y): float
    {
        $rgb = imagecolorat($image, $x, $y);

        return 0.299 * (($rgb >> 16) & 0xFF) + 0.587 * (($rgb >> 8) & 0xFF) + 0.114 * ($rgb & 0xFF);
    }
}
