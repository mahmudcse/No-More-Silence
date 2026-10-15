<?php

declare(strict_types=1);

use App\Enums\ReportCategory;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\Division;
use App\Models\Incident;
use App\Models\Report;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Storage::fake('media');
    RateLimiter::clear('submission:ip:'.hash_hmac('sha256', '127.0.0.1', (string) config('app.key')));
});

test('public submission creates new report and returns tracking code', function (): void {
    $division = Division::factory()->create();
    $district = District::factory()->create(['division_id' => $division->id]);
    $incident = Incident::factory()->create(['district_id' => $district->id]);

    $file = UploadedFile::fake()->image('evidence.jpg', 640, 480);

    $response = postJson(route('api.v1.submissions.store'), [
        'submitted_profile_url' => 'https://www.facebook.com/bad.actor.123',
        'submitted_comment_url' => 'https://www.facebook.com/post1/comments/999',
        'comment_text' => 'This is a test harmful apologia comment',
        'category' => ReportCategory::RapeApologia->value,
        'incident_id' => $incident->id,
        'reporter_email' => 'citizen@example.com',
        'evidence' => [$file],
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'already_reported' => false,
        ])
        ->assertJsonStructure([
            'tracking_code',
            'uuid',
            'message',
        ]);

    $data = $response->json();
    expect($data['tracking_code'])->toBeString()->toHaveLength(32);

    // Verify report in DB
    $report = Report::query()->where('uuid', $data['uuid'])->first();
    expect($report)->not->toBeNull()
        ->and($report->comment_text)->toBe('This is a test harmful apologia comment')
        ->and($report->workflow_status)->toBe(WorkflowStatus::Submitted)
        ->and($report->visibility)->toBe(Visibility::Hidden);
});

test('instant duplicate detection identifies existing submitted comment URL', function (): void {
    $existingReport = Report::factory()->create([
        'submitted_comment_url' => 'https://www.facebook.com/post1/comments/already-reported',
        'workflow_status' => WorkflowStatus::Verified,
    ]);

    $response = postJson(route('api.v1.submissions.store'), [
        'submitted_profile_url' => 'https://www.facebook.com/another.actor',
        'submitted_comment_url' => 'https://www.facebook.com/post1/comments/already-reported',
        'evidence' => [UploadedFile::fake()->image('test.jpg')],
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'already_reported' => true,
            'workflow_status' => 'verified',
        ]);
});

test('submission rate limit blocks more than 10 submissions per day per ip', function (): void {
    $ipHash = hash_hmac('sha256', '127.0.0.1', (string) config('app.key'));
    $rateLimitKey = "submission:ip:{$ipHash}";

    // Simulate 10 hits
    for ($i = 0; $i < 10; $i++) {
        RateLimiter::hit($rateLimitKey, 86400);
    }

    $response = postJson(route('api.v1.submissions.store'), [
        'submitted_profile_url' => 'https://www.facebook.com/spam.actor',
        'evidence' => [UploadedFile::fake()->image('test.jpg')],
    ]);

    $response->assertStatus(429);
});

test('tracking endpoint returns live report pipeline status', function (): void {
    $code = 'NMS-TEST-1234';
    $codeHash = hash('sha256', $code);

    $report = Report::factory()->create([
        'tracking_code_hash' => $codeHash,
        'workflow_status' => WorkflowStatus::InVerification,
        'visibility' => Visibility::Hidden,
        'category' => ReportCategory::VictimBlaming,
    ]);

    getJson(route('api.v1.submissions.track', ['code' => $code]))
        ->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'tracking_code' => $code,
                'uuid' => $report->uuid,
                'status' => 'in_verification',
                'category' => 'victim_blaming',
            ],
        ]);
});

test('tracking endpoint returns 404 for nonexistent code', function (): void {
    getJson(route('api.v1.submissions.track', ['code' => 'NMS-NONEXISTENT']))
        ->assertNotFound();
});
