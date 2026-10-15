<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Storage::fake('media');
});

test('security headers are present on all http responses', function (): void {
    $response = get(route('home'));

    $response->assertOk();
    $headers = $response->headers;

    expect($headers->get('x-content-type-options'))->toBe('nosniff')
        ->and($headers->get('x-frame-options'))->toBe('SAMEORIGIN')
        ->and($headers->get('x-xss-protection'))->toBe('1; mode=block')
        ->and($headers->get('referrer-policy'))->toBe('strict-origin-when-cross-origin')
        ->and($headers->get('permissions-policy'))->toContain('camera=()')
        ->and($headers->get('content-security-policy'))->toContain("default-src 'self'");
});

test('unauthenticated users cannot access internal dashboard or profile routes', function (): void {
    get(route('dashboard'))->assertRedirect(route('login'));
    get(route('profile.edit'))->assertRedirect(route('login'));
});

test('submission rejects non-image or executable files', function (): void {
    $dangerousFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');

    $response = postJson(route('api.v1.submissions.store'), [
        'submitted_profile_url' => 'https://www.facebook.com/test.profile',
        'evidence' => [$dangerousFile],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['evidence.0']);
});

test('submission rejects files exceeding 10MB limit', function (): void {
    $largeFile = UploadedFile::fake()->create('huge-screenshot.png', 11000, 'image/png');

    $response = postJson(route('api.v1.submissions.store'), [
        'submitted_profile_url' => 'https://www.facebook.com/test.profile',
        'evidence' => [$largeFile],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['evidence.0']);
});

test('public api stats responses include cdn cache control headers', function (): void {
    $response = get(route('api.v1.stats.national'));

    $response->assertOk();
    expect($response->headers->get('cache-control'))->toContain('public')
        ->and($response->headers->get('cache-control'))->toContain('s-maxage=300');
});
