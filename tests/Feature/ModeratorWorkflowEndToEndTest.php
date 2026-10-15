<?php

declare(strict_types=1);

use App\Actions\Evidence\RedactEvidence;
use App\Actions\Reports\PublishReport;
use App\Actions\Reports\SubmitReport;
use App\Actions\Reports\VerifyReport;
use App\Data\NewReportData;
use App\Enums\ReportCategory;
use App\Enums\VerificationCheck;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Support\ReportLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('super_admin');
    Permission::findOrCreate('triage_report');
    Permission::findOrCreate('verify_report');
    Permission::findOrCreate('publish_report');
    Permission::findOrCreate('redact_evidence');
});

test('end-to-end moderation lifecycle: submission to verification, redaction, two-person publishing, and audit log', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    // Setup actors
    $triageAdmin = User::factory()->withMfa()->create()->givePermissionTo('triage_report');
    $verifierAdmin = User::factory()->withMfa()->create()->givePermissionTo('verify_report');
    $publisherAdmin = User::factory()->withMfa()->create()->givePermissionTo('publish_report');

    // 1. Ingestion / Submission
    $submitted = app(SubmitReport::class)->handle(new NewReportData(
        submittedProfileUrl: 'https://m.facebook.com/johndoe?mibextid=123',
        submittedCommentUrl: 'https://facebook.com/post/123/comment/456',
        commentText: 'This is a verbatim threatening comment',
        category: ReportCategory::SexualThreat,
        severity: 3,
    ));

    $report = $submitted->report;
    expect($report->workflow_status)->toBe(WorkflowStatus::Submitted);
    expect($report->visibility)->toBe(Visibility::Hidden);

    // 2. Attach Evidence & Verify EXIF/pHash processing
    $report->addMedia(UploadedFile::fake()->image('threat.png', 100, 100))
        ->toMediaCollection('original');

    $originalMedia = $report->fresh()->getFirstMedia('original');
    expect($originalMedia)->not->toBeNull();
    expect($originalMedia->getCustomProperty('sha256'))->not->toBeNull();
    expect($originalMedia->getCustomProperty('phash'))->not->toBeNull();

    // 3. Reviewer acquires lock
    $lock = app(ReportLock::class);
    expect($lock->acquire($report, $verifierAdmin))->toBeTrue();
    expect($lock->isLockedForOthers($report, $verifierAdmin))->toBeFalse();
    expect($lock->isLockedForOthers($report, $publisherAdmin))->toBeTrue();

    // 4. Link Profile & Incident
    $profile = FacebookProfile::factory()->create([
        'canonical_url' => 'https://www.facebook.com/johndoe',
        'public_reports_count' => 0,
    ]);
    $incident = Incident::factory()->create();

    $report->update([
        'facebook_profile_id' => $profile->id,
        'incident_id' => $incident->id,
        'workflow_status' => WorkflowStatus::InVerification,
    ]);

    // 5. Redaction: blackout sensitive areas and store in redacted collection
    $redactedMedia = app(RedactEvidence::class)->handle($report, $originalMedia, [
        ['x' => 10, 'y' => 10, 'width' => 40, 'height' => 20],
    ]);
    expect($redactedMedia->collection_name)->toBe('redacted');
    expect($report->fresh()->getMedia('redacted'))->toHaveCount(1);

    // 6. Verification with 8-point checklist
    $checklist = [];
    foreach (VerificationCheck::cases() as $check) {
        $checklist[$check->value] = true;
    }

    $verifiedReport = app(VerifyReport::class)->handle(
        report: $report,
        admin: $verifierAdmin,
        checklist: $checklist,
        note: 'All 8 points verified against permalink'
    );
    expect($verifiedReport->workflow_status)->toBe(WorkflowStatus::Verified);
    expect($verifiedReport->verified_by)->toBe($verifierAdmin->id);

    // 7. Enforce Two-Person Rule: verifying admin CANNOT publish
    $policy = new ReportPolicy;
    expect($policy->publish($verifierAdmin, $verifiedReport))->toBeFalse();
    expect(fn () => app(PublishReport::class)->handle($verifiedReport, $verifierAdmin, Visibility::Public))
        ->toThrow(DomainException::class, 'The verifying admin cannot publish the same report.');

    // 8. Different admin publishes report
    expect($policy->publish($publisherAdmin, $verifiedReport))->toBeTrue();
    $publishedReport = app(PublishReport::class)->handle($verifiedReport, $publisherAdmin, Visibility::Public);
    expect($publishedReport->visibility)->toBe(Visibility::Public);
    expect($publishedReport->published_by)->toBe($publisherAdmin->id);

    // 9. Profile counter increments automatically via domain event listener
    expect($profile->fresh()->public_reports_count)->toBe(1);

    // 10. Audit log verification
    $activities = Activity::forSubject($report)->get();
    expect($activities->count())->toBeGreaterThan(0);

    // 11. Lock released after completion
    $lock->release($report, $verifierAdmin);
    expect($lock->holderId($report))->toBeNull();
});
