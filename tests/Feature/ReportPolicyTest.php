<?php

declare(strict_types=1);

use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Permission::findOrCreate('publish_report');
    Permission::findOrCreate('verify_report');
});

it('rejects publication by the same admin who verified', function (): void {
    $verifier = User::factory()->create()->givePermissionTo('publish_report');
    $report = Report::factory()->verified()->create(['verified_by' => $verifier->id]);

    expect((new ReportPolicy)->publish($verifier, $report))->toBeFalse();
});

it('allows publication by a different admin', function (): void {
    $verifier = User::factory()->create();
    $publisher = User::factory()->create()->givePermissionTo('publish_report');
    $report = Report::factory()->verified()->create(['verified_by' => $verifier->id]);

    expect((new ReportPolicy)->publish($publisher, $report))->toBeTrue();
});

it('rejects review by the admin who submitted the report', function (): void {
    $admin = User::factory()->create()->givePermissionTo('verify_report');
    $report = Report::factory()->create(['submitted_by' => $admin->id]);

    expect((new ReportPolicy)->verify($admin, $report))->toBeFalse();
});

it('allows review by an admin who did not submit the report', function (): void {
    $submitter = User::factory()->create();
    $reviewer = User::factory()->create()->givePermissionTo('verify_report');
    $report = Report::factory()->create(['submitted_by' => $submitter->id]);

    expect((new ReportPolicy)->verify($reviewer, $report))->toBeTrue();
});
