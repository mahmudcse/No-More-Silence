<?php

declare(strict_types=1);

use App\Enums\ReportCategory;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Report;
use App\Models\Reporter;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('magic link generation returns signed verification URL', function (): void {
    $response = postJson(route('api.v1.auth.magic-link'), [
        'email' => 'whistleblower@example.com',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'verify_url',
            'message',
        ]);

    $verifyUrl = (string) $response->json('verify_url');
    expect($verifyUrl)->toContain('/api/v1/auth/verify/')
        ->and($verifyUrl)->toContain('signature=');
});

test('valid signed magic link verifies reporter and generates token', function (): void {
    $reporter = Reporter::factory()->create(['email' => 'reporter1@example.com']);

    $signedUrl = URL::temporarySignedRoute(
        'api.v1.auth.verify',
        now()->addMinutes(30),
        ['id' => $reporter->id]
    );

    $response = getJson($signedUrl);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'reporter' => [
                'id' => $reporter->id,
                'email' => 'reporter1@example.com',
            ],
        ])
        ->assertJsonStructure([
            'token',
            'reporter',
            'message',
        ]);
});

test('invalid or unsigned url fails verification with 401', function (): void {
    $reporter = Reporter::factory()->create();

    getJson(route('api.v1.auth.verify', ['id' => $reporter->id]))
        ->assertStatus(401);
});

test('authenticated reporter can view their submitted reports', function (): void {
    $reporter = Reporter::factory()->create(['email' => 'contributor@example.com']);
    $token = $reporter->createToken('test_token')->plainTextToken;

    $report = Report::factory()->create([
        'reporter_id' => $reporter->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'category' => ReportCategory::SexualThreat,
        'comment_text' => 'Direct threat against victim',
    ]);

    $response = getJson(route('api.v1.me.submissions'), [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                [
                    'uuid' => $report->uuid,
                    'workflow_status' => 'verified',
                    'category' => 'sexual_threat',
                    'comment_text' => 'Direct threat against victim',
                ],
            ],
        ]);
});
