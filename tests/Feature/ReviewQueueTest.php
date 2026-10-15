<?php

declare(strict_types=1);

use App\Enums\ReportCategory;
use App\Enums\VerificationCheck;
use App\Enums\WorkflowStatus;
use App\Filament\Pages\ReviewQueue;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use App\Support\ReportLock;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Permission::findOrCreate('verify_report');
    Permission::findOrCreate('triage_report');
    Role::findOrCreate('super_admin');
});

test('guests and users without permissions cannot access review queue', function (): void {
    get('/admin/review-queue')->assertRedirect('/admin/login');

    $user = User::factory()->withMfa()->create();
    actingAs($user)->get('/admin/review-queue')->assertForbidden();
});

test('verifier can access review queue page', function (): void {
    $verifier = User::factory()->withMfa()->create()->givePermissionTo('verify_report');

    actingAs($verifier)
        ->get('/admin/review-queue')
        ->assertOk();
});

test('review queue acquires lock on report for current admin', function (): void {
    $admin1 = User::factory()->create()->givePermissionTo('verify_report');
    $admin2 = User::factory()->create()->givePermissionTo('verify_report');
    $report = Report::factory()->create(['workflow_status' => WorkflowStatus::InVerification]);

    actingAs($admin1);
    Livewire::test(ReviewQueue::class, ['report' => $report->id])
        ->assertSet('isLockedByOther', false);

    expect(app(ReportLock::class)->holderId($report))->toBe($admin1->id);

    // Second admin opens the same report
    actingAs($admin2);
    Livewire::test(ReviewQueue::class, ['report' => $report->id])
        ->assertSet('isLockedByOther', true)
        ->assertSet('lockedByName', $admin1->name);
});

test('review queue verifies report when all checks are completed', function (): void {
    $admin = User::factory()->create()->givePermissionTo('verify_report');
    $profile = FacebookProfile::factory()->create();
    $incident = Incident::factory()->create();
    $report = Report::factory()->create([
        'workflow_status' => WorkflowStatus::InVerification,
        'facebook_profile_id' => $profile->id,
        'incident_id' => $incident->id,
        'category' => ReportCategory::VictimBlaming,
        'submitted_by' => null,
    ]);

    $checklist = [];
    foreach (VerificationCheck::cases() as $check) {
        $checklist[$check->value] = true;
    }

    actingAs($admin);
    Livewire::test(ReviewQueue::class, ['report' => $report->id])
        ->set('checklist', $checklist)
        ->call('verify')
        ->assertHasNoErrors();

    expect($report->fresh()->workflow_status)->toBe(WorkflowStatus::Verified);
});
