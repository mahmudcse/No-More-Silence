<?php

declare(strict_types=1);

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Http\Controllers\Api\V1\SearchController;
use App\Models\District;
use App\Models\Division;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;

use function Pest\Laravel\getJson;

function createApiTestDistrict(): District
{
    $division = Division::factory()->create(['name_en' => 'Chattogram', 'name_bn' => 'চট্টগ্রাম']);

    return District::factory()->create([
        'division_id' => $division->id,
        'name_en' => 'Chattogram',
        'name_bn' => 'চট্টগ্রাম',
        'slug' => 'chattogram',
        'population' => 9169464,
    ]);
}

test('api stats endpoint returns national metrics', function (): void {
    Report::factory()->create([
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
    ]);

    getJson(route('api.v1.stats.national'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'received',
                'verified',
                'public',
                'profiles',
                'districts',
                'received_bn',
                'verified_bn',
                'public_bn',
                'profiles_bn',
                'districts_bn',
            ],
        ]);
});

test('api stats districts endpoint returns district breakdown by mode', function (): void {
    createApiTestDistrict();

    getJson(route('api.v1.stats.districts', ['mode' => 'incident']))
        ->assertOk()
        ->assertJson([
            'mode' => 'incident',
        ])
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'slug',
                    'name_en',
                    'name_bn',
                    'division',
                    'population',
                    'count',
                    'count_bn',
                    'rate_per_100k',
                    'rate_per_100k_bn',
                ],
            ],
        ]);
});

test('api featured reports returns only published and public records', function (): void {
    $district = createApiTestDistrict();
    $profile = FacebookProfile::factory()->create(['public_reports_count' => 1]);
    $incident = Incident::factory()->create(['district_id' => $district->id]);

    $publicReport = Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'incident_id' => $incident->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'comment_text' => 'পাবলিক মন্তব্য',
        'published_at' => now(),
    ]);

    $hiddenReport = Report::factory()->create([
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Hidden,
        'comment_text' => 'গোপন মন্তব্য',
    ]);

    $response = getJson(route('api.v1.reports.featured'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'comment_text',
                    'category',
                    'verified_at',
                    'published_at',
                    'profile',
                    'incident',
                ],
            ],
        ]);

    /** @var array<int, array<string, mixed>> $data */
    $data = $response->json('data') ?? [];
    $uuids = array_column($data, 'uuid');
    expect($uuids)->toContain($publicReport->uuid)
        ->and($uuids)->not->toContain($hiddenReport->uuid);
});

test('api reports index endpoint returns paginated recent reports with meta', function (): void {
    Report::factory()->create([
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'comment_text' => 'পাবলিক মন্তব্য',
    ]);

    getJson(route('api.v1.reports.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid',
                    'comment_text',
                    'workflow_status',
                    'is_verified',
                    'photos',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'has_more',
                'total',
            ],
        ]);
});

test('api report show returns public report and rejects hidden report', function (): void {
    $publicReport = Report::factory()->create([
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
    ]);

    $hiddenReport = Report::factory()->create([
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Hidden,
    ]);

    getJson(route('api.v1.reports.show', ['uuid' => $publicReport->uuid]))
        ->assertOk()
        ->assertJsonPath('data.uuid', $publicReport->uuid);

    getJson(route('api.v1.reports.show', ['uuid' => $hiddenReport->uuid]))
        ->assertNotFound();
});

test('api search endpoint returns results and same name warning for text queries', function (): void {
    FacebookProfile::factory()->create([
        'current_display_name' => 'Tanvir Hasan',
        'display_name_normalized' => 'tanvir hasan',
        'public_reports_count' => 2,
    ]);

    getJson(route('api.v1.search', ['q' => 'Tanvir']))
        ->assertOk()
        ->assertJson([
            'type' => 'name',
        ])
        ->assertJsonPath('warning', SearchController::SAME_NAME_WARNING_BN)
        ->assertJsonCount(1, 'data');
});

test('api profile show returns profile details without avatar', function (): void {
    $profile = FacebookProfile::factory()->create([
        'current_display_name' => 'Profile User',
        'public_reports_count' => 1,
    ]);

    getJson(route('api.v1.profiles.show', ['id' => $profile->uuid]))
        ->assertOk()
        ->assertJsonPath('data.uuid', $profile->uuid)
        ->assertJsonPath('data.display_name', 'Profile User')
        ->assertJsonMissing(['avatar', 'photo', 'avatar_url']);
});

test('openapi documentation endpoints render successfully', function (): void {
    \Pest\Laravel\get('/docs/api')->assertOk();
    getJson('/docs/api.json')->assertOk()->assertJsonStructure(['openapi', 'info', 'paths']);
});
