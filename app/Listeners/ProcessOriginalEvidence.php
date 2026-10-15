<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\PerceptualHasher;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Prepares every uploaded original screenshot for evidence use:
 * re-encodes it with GD (dropping EXIF/GPS metadata that could identify the reporter),
 * then records a sha256 integrity hash and a perceptual hash for duplicate detection.
 */
class ProcessOriginalEvidence
{
    public function __construct(private readonly PerceptualHasher $hasher) {}

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if ($media->collection_name !== 'original') {
            return;
        }

        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $path = $media->getPath();

        Image::useImageDriver(ImageDriver::Gd)->loadFile($path)->quality(90)->save($path);

        clearstatcache(true, $path);

        $media->size = (int) filesize($path);
        $media->setCustomProperty('sha256', hash_file('sha256', $path));
        $media->setCustomProperty('phash', $this->hasher->hash($path));
        $media->save();
    }
}
