<?php

declare(strict_types=1);

use App\Models\Appeal;
use App\Models\FacebookProfile;
use App\Models\Report;

use function Pest\Laravel\postJson;

test('public user can submit right-of-reply appeal linked to profile', function (): void {
    $profile = FacebookProfile::factory()->create([
        'canonical_url' => 'https://www.facebook.com/disputed.profile',
    ]);
    $report = Report::factory()->create([
        'facebook_profile_id' => $profile->id,
    ]);

    $response = postJson(route('api.v1.appeals.store'), [
        'facebook_profile_url' => 'https://www.facebook.com/disputed.profile',
        'report_uuid' => $report->uuid,
        'contact' => 'appeals@example.com',
        'message' => 'My account was hacked during the incident period and I have reported it to police GD #12345.',
        'ownership_proof' => 'GD copy link: https://example.com/gd-12345.pdf',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'appeal_id',
            'message',
        ]);

    $appealId = $response->json('appeal_id');

    $appeal = Appeal::query()->findOrFail($appealId);
    expect($appeal->facebook_profile_id)->toBe($profile->id)
        ->and($appeal->report_id)->toBe($report->id)
        ->and($appeal->contact)->toBe('appeals@example.com')
        ->and($appeal->status->value)->toBe('open');
});

test('appeal validation fails without contact or message', function (): void {
    postJson(route('api.v1.appeals.store'), [
        'facebook_profile_url' => 'https://www.facebook.com/some.user',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['contact', 'message']);
});
