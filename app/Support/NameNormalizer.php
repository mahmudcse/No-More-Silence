<?php

declare(strict_types=1);

namespace App\Support;

use Normalizer;

/**
 * Normalises display names (Bangla and Latin script) into a search key:
 * Unicode NFKC, lowercase, punctuation removed, whitespace collapsed.
 */
final class NameNormalizer
{
    public function normalize(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = Normalizer::normalize($name, Normalizer::FORM_KC) ?: $name;
        $name = mb_strtolower($name);
        $name = (string) preg_replace('/[^\p{L}\p{M}\p{N}\s]+/u', ' ', $name);
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));

        return $name !== '' ? $name : null;
    }
}
