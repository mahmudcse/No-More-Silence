<?php

declare(strict_types=1);

use App\Actions\Evidence\RedactEvidence;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('creates a redacted media copy while keeping the original private', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $report = Report::factory()->create();
    $report
        ->addMedia(UploadedFile::fake()->image('evidence.png', 20, 20))
        ->toMediaCollection('original');

    $original = $report->getFirstMedia('original');

    expect($original)->not->toBeNull();

    $redacted = app(RedactEvidence::class)->handle($report, $original, [
        ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10],
    ]);

    expect($redacted->collection_name)->toBe('redacted');
    expect($report->fresh()->getMedia('redacted'))->toHaveCount(1);
    expect($report->fresh()->getFirstMedia('redacted')->mime_type)->toBe('image/webp');
});
