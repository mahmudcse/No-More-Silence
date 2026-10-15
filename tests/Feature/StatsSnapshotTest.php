<?php

declare(strict_types=1);

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\Division;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use App\Models\StatsSnapshot;
use App\Services\StatsService;
use Illuminate\Support\Carbon;

use function Pest\Laravel\artisan;

function createStatsTestDistrict(): District
{
    $division = Division::factory()->create(['name_en' => 'Sylhet', 'name_bn' => 'সিলেট']);

    return District::factory()->create([
        'division_id' => $division->id,
        'name_en' => 'Sylhet',
        'name_bn' => 'সিলেট',
        'slug' => 'sylhet',
        'population' => 3857037,
    ]);
}

test('stats snapshot command generates daily snapshots for national and districts', function (): void {
    $district = createStatsTestDistrict();

    $profile = FacebookProfile::factory()->create([
        'district_id' => $district->id,
        'public_reports_count' => 1,
    ]);

    $incident = Incident::factory()->create([
        'district_id' => $district->id,
    ]);

    Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'incident_id' => $incident->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
    ]);

    artisan('stats:snapshot')
        ->assertSuccessful();

    $today = Carbon::today()->toDateString();

    $nationalSnapshot = StatsSnapshot::query()
        ->whereDate('date', $today)
        ->whereNull('district_id')
        ->first();

    expect($nationalSnapshot)->not->toBeNull()
        ->and($nationalSnapshot->received)->toBeGreaterThanOrEqual(1)
        ->and($nationalSnapshot->public)->toBeGreaterThanOrEqual(1)
        ->and($nationalSnapshot->profiles)->toBeGreaterThanOrEqual(1);

    $districtSnapshot = StatsSnapshot::query()
        ->whereDate('date', $today)
        ->where('district_id', $district->id)
        ->first();

    expect($districtSnapshot)->not->toBeNull()
        ->and($districtSnapshot->public)->toBeGreaterThanOrEqual(1);
});

test('stats service returns cached national metrics and district rates', function (): void {
    createStatsTestDistrict();

    $service = app(StatsService::class);

    $national = $service->getNationalStats();
    expect($national)->toHaveKeys(['received', 'verified', 'public', 'profiles', 'districts', 'received_bn']);

    $districts = $service->getDistrictStats('incident');
    expect($districts)->toBeArray()
        ->and(count($districts))->toBeGreaterThanOrEqual(1);

    $first = $districts[0];
    expect($first)->toHaveKeys(['id', 'code', 'slug', 'name_en', 'name_bn', 'population', 'count', 'rate_per_100k']);
});
