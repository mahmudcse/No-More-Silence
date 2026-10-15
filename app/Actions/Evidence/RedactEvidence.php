<?php

declare(strict_types=1);

namespace App\Actions\Evidence;

use App\Models\Report;
use GdImage;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class RedactEvidence
{
    /**
     * Applies solid black boxes over the source media and stores a WebP copy
     * in the public "redacted" collection. The original stays private.
     *
     * @param  array<int, array{x: int, y: int, width: int, height: int}>  $rectangles
     */
    public function handle(Report $report, Media $media, array $rectangles): Media
    {
        if ($media->collection_name !== 'original') {
            throw new InvalidArgumentException('Only original media can be redacted.');
        }

        $image = @imagecreatefromstring((string) file_get_contents($media->getPath()));

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('The media file is not a readable image.');
        }

        $black = imagecolorallocate($image, 0, 0, 0);

        foreach ($rectangles as $rectangle) {
            $x = (int) $rectangle['x'];
            $y = (int) $rectangle['y'];
            $width = (int) $rectangle['width'];
            $height = (int) $rectangle['height'];

            imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $black);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'redacted').'.webp';
        imagewebp($image, $tempPath, 90);
        imagedestroy($image);

        try {
            return $report
                ->addMedia($tempPath)
                ->usingFileName($media->uuid.'.webp')
                ->toMediaCollection('redacted');
        } finally {
            @unlink($tempPath);
        }
    }
}
