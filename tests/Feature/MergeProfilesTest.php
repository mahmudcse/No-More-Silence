<?php

declare(strict_types=1);

use App\Actions\Profiles\MergeProfiles;
use App\Events\ProfileMerged;
use App\Models\Appeal;
use App\Models\FacebookProfile;
use App\Models\ProfileAlias;
use App\Models\ProfileSnapshot;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('merges secondary profile relationships into primary profile and deletes secondary', function (): void {
    Event::fake([ProfileMerged::class]);

    $admin = User::factory()->create();
    $primary = FacebookProfile::factory()->create(['current_display_name' => 'Primary Profile']);
    $secondary = FacebookProfile::factory()->create(['current_display_name' => 'Secondary Profile']);

    $alias = ProfileAlias::factory()->create(['facebook_profile_id' => $secondary->id]);
    $snapshot = ProfileSnapshot::factory()->create(['facebook_profile_id' => $secondary->id]);
    $report = Report::factory()->create(['facebook_profile_id' => $secondary->id]);
    $appeal = Appeal::factory()->create(['facebook_profile_id' => $secondary->id]);

    $action = app(MergeProfiles::class);
    $result = $action->handle($primary, $secondary, $admin);

    expect($result->id)->toBe($primary->id);
    expect($alias->fresh()->facebook_profile_id)->toBe($primary->id);
    expect($snapshot->fresh()->facebook_profile_id)->toBe($primary->id);
    expect($report->fresh()->facebook_profile_id)->toBe($primary->id);
    expect($appeal->fresh()->facebook_profile_id)->toBe($primary->id);
    expect(FacebookProfile::find($secondary->id))->toBeNull();

    Event::assertDispatched(ProfileMerged::class, function (ProfileMerged $event) use ($primary, $secondary, $admin): bool {
        return $event->primary->id === $primary->id
            && $event->mergedProfileId === $secondary->id
            && $event->admin->id === $admin->id;
    });
});

test('cannot merge profile into itself', function (): void {
    $admin = User::factory()->create();
    $profile = FacebookProfile::factory()->create();

    app(MergeProfiles::class)->handle($profile, $profile, $admin);
})->throws(DomainException::class, 'A profile cannot be merged into itself.');
