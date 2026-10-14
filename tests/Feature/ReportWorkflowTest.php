<?php

declare(strict_types=1);

use App\Actions\Reports\PublishReport;
use App\Actions\Reports\RetractReport;
use App\Actions\Reports\SubmitReport;
use App\Actions\Reports\VerifyReport;
use App\Data\NewReportData;
use App\Enums\ReportCategory;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use App\Models\Report;
use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('submits a report with a hashed tracking code', function (): void {
    $result = app(SubmitReport::class)->handle(new NewReportData(
        submittedProfileUrl: 'https://www.facebook.com/johndoe',
        commentText: 'A verbatim comment',
        category: ReportCategory::VictimBlaming,
        severity: 2,
    ));

    expect($result->trackingCode)->toHaveLength(32);
    expect($result->report->workflow_status)->toBe(WorkflowStatus::Submitted);
    expect($result->report->tracking_code_hash)->toBe(hash('sha256', $result->trackingCode));
});

it('verifies a report and records a review with the checklist', function (): void {
    $admin = User::factory()->create();
    $report = Report::factory()->create(['workflow_status' => WorkflowStatus::InVerification]);

    app(VerifyReport::class)->handle($report, $admin, ['permalink_exists' => true, 'text_match' => true]);

    $report->refresh();

    expect($report->workflow_status)->toBe(WorkflowStatus::Verified);
    expect($report->verified_by)->toBe($admin->id);
    expect($report->reviews)->toHaveCount(1);
    expect($report->reviews->first()->to_status)->toBe('verified');
    expect($report->reviews->first()->checklist)->toBe(['permalink_exists' => true, 'text_match' => true]);
});

it('publishes a verified report and increments the profile counter', function (): void {
    $profile = FacebookProfile::factory()->create(['public_reports_count' => 0]);
    $verifier = User::factory()->create();
    $publisher = User::factory()->create();
    $report = Report::factory()->verified()->create([
        'facebook_profile_id' => $profile->id,
        'verified_by' => $verifier->id,
    ]);

    app(PublishReport::class)->handle($report, $publisher, Visibility::Public);

    expect($report->fresh()->visibility)->toBe(Visibility::Public);
    expect($profile->fresh()->public_reports_count)->toBe(1);
});

it('rejects publication by the same admin who verified', function (): void {
    $admin = User::factory()->create();
    $report = Report::factory()->verified()->create(['verified_by' => $admin->id]);

    expect(fn () => app(PublishReport::class)->handle($report, $admin, Visibility::Public))
        ->toThrow(DomainException::class);
});

it('retracts a public report and decrements the profile counter', function (): void {
    $profile = FacebookProfile::factory()->create(['public_reports_count' => 1]);
    $report = Report::factory()->verified()->create([
        'facebook_profile_id' => $profile->id,
        'visibility' => Visibility::Public,
    ]);

    app(RetractReport::class)->handle($report, User::factory()->create(), 'Error found after publication');

    expect($report->fresh()->workflow_status)->toBe(WorkflowStatus::Retracted);
    expect($report->fresh()->visibility)->toBe(Visibility::Hidden);
    expect($profile->fresh()->public_reports_count)->toBe(0);
});
