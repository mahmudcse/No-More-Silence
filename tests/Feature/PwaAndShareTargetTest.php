<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    Storage::fake('local');
});

test('pwa webmanifest is publicly accessible and contains web share target', function (): void {
    $response = get('/manifest.webmanifest');

    $response->assertOk();
    $content = (string) file_get_contents(public_path('manifest.webmanifest'));
    $data = json_decode($content, true);

    expect($data)->toBeArray()
        ->and($data['name'])->toBe('No More Silence — স্বাধীন প্রমাণ ও আর্কাইভ')
        ->and($data['short_name'])->toBe('No More Silence')
        ->and($data['display'])->toBe('standalone')
        ->and($data['icons'])->toBeArray()
        ->and(count($data['icons']))->toBeGreaterThan(0)
        ->and($data['share_target'])->toBeArray()
        ->and($data['share_target']['action'])->toBe('/submit/share-target')
        ->and($data['share_target']['method'])->toBe('POST');
});

test('digital asset links is served for android twa verification', function (): void {
    $response = get('/.well-known/assetlinks.json');

    $response->assertOk();
    $content = (string) file_get_contents(public_path('.well-known/assetlinks.json'));
    $data = json_decode($content, true);

    expect($data)->toBeArray()
        ->and(count($data))->toBeGreaterThan(0)
        ->and($data[0]['target']['package_name'])->toBe('org.nomoresilence.twa');
});

test('service worker and offline shell are publicly accessible', function (): void {
    get('/sw.js')->assertOk();

    $offline = get('/offline.html');
    $offline->assertOk();
    $content = (string) file_get_contents(public_path('offline.html'));
    expect($content)->toContain('১০৯')
        ->toContain('৯৯৯')
        ->toContain('১০৯৮');
});

test('web share target receives mobile share and pre-fills submission wizard', function (): void {
    $file = UploadedFile::fake()->image('shared-screenshot.png', 800, 600);

    $response = post(route('submit.share-target'), [
        'title' => 'Discovered Facebook Comment',
        'text' => 'Take a look at this post: https://www.facebook.com/violator.user/posts/123456789',
        'url' => 'https://www.facebook.com/violator.user/posts/123456789',
        'evidence' => [$file],
    ]);

    $response->assertRedirect();
    $redirectUrl = (string) $response->headers->get('Location');
    expect($redirectUrl)->toContain('/submit?share_id=');

    // Follow redirect to GET /submit?share_id=...
    $pageResponse = get($redirectUrl);
    $pageResponse->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Submissions/Create')
            ->has('sharedData', fn ($shared) => $shared
                ->where('text', 'Take a look at this post: https://www.facebook.com/violator.user/posts/123456789')
                ->where('extracted_url', 'https://www.facebook.com/violator.user/posts/123456789')
                ->has('files', 1)
                ->etc()
            )
        );
});

test('shared file endpoint serves stored preview and guards against path traversal', function (): void {
    $file = UploadedFile::fake()->image('preview.jpg');

    $response = post(route('submit.share-target'), [
        'evidence' => [$file],
    ]);

    $redirectUrl = (string) $response->headers->get('Location');
    parse_str((string) parse_url($redirectUrl, PHP_URL_QUERY), $queryParams);
    $shareId = $queryParams['share_id'];

    $sharedData = session()->get("share_target:{$shareId}");
    $filename = $sharedData['files'][0]['filename'];

    // Can access shared file preview
    get(route('submissions.shared-file', ['shareId' => $shareId, 'filename' => $filename]))
        ->assertOk();

    // Rejects path traversal attempt
    get(route('submissions.shared-file', ['shareId' => $shareId, 'filename' => '../../etc/passwd']))
        ->assertNotFound();
});
