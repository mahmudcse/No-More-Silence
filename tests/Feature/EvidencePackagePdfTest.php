<?php

declare(strict_types=1);

use App\Actions\Export\ExportEvidencePackage;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use App\Models\Report;

use function Pest\Laravel\get;

test('export evidence package generates valid PDF stream for profile', function (): void {
    $profile = FacebookProfile::factory()->create([
        'current_display_name' => 'John Doe Subject',
        'canonical_url' => 'https://www.facebook.com/john.doe.subject',
        'public_reports_count' => 1,
    ]);

    Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'comment_text' => 'Recorded comment for PDF dossier',
    ]);

    $action = app(ExportEvidencePackage::class);
    $response = $action->download($profile);

    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->getContent())->toBeString()
        ->and(str_starts_with((string) $response->getContent(), '%PDF-'))->toBeTrue();
});

test('api endpoint downloads evidence package pdf', function (): void {
    $profile = FacebookProfile::factory()->create([
        'public_reports_count' => 1,
    ]);

    Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
    ]);

    $response = get(route('api.v1.profiles.evidence-package', ['id' => $profile->uuid]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and(str_starts_with((string) $response->getContent(), '%PDF-'))->toBeTrue();
});
