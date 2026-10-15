<?php

declare(strict_types=1);

use App\Enums\ReportCategory;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\Division;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

beforeEach(function (): void {
    // Clean cache
    Cache::flush();
});

test('open data summary api returns aggregated statistics adhering to section 14 privacy', function (): void {
    $division = Division::factory()->create();
    $district = District::factory()->create(['division_id' => $division->id, 'population' => 200000]);
    $incident = Incident::factory()->create(['district_id' => $district->id]);
    $profile = FacebookProfile::factory()->create(['district_id' => $district->id]);

    Report::factory()->create([
        'incident_id' => $incident->id,
        'facebook_profile_id' => $profile->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'category' => ReportCategory::SexualHarassment,
        'verified_at' => Carbon::now(),
    ]);

    $response = getJson(route('api.v1.open-data.summary'));

    $response->assertOk()
        ->assertJsonStructure([
            'metadata' => ['title', 'license', 'legal_compliance', 'generated_at'],
            'totals' => ['verified_reports', 'incidents_covered', 'districts_affected', 'appeals_processed'],
            'categories',
            'districts',
            'monthly_trends',
            'appeals_resolution',
        ]);

    expect($response->json('metadata.license'))->toContain('CC-BY 4.0')
        ->and($response->json('totals.verified_reports'))->toBeGreaterThanOrEqual(1);
});

test('open data csv endpoints stream valid formatted csv datasets', function (): void {
    $division = Division::factory()->create();
    $district = District::factory()->create(['division_id' => $division->id]);

    $districtsResponse = get(route('api.v1.open-data.districts-csv'));
    $districtsResponse->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertSee('bbs_code,district_en,district_bn,division_en,population,verified_reports,rate_per_100k');

    $categoriesResponse = get(route('api.v1.open-data.categories-csv'));
    $categoriesResponse->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertSee('category_key,category_en,category_bn,verified_reports,percentage');

    $monthlyResponse = get(route('api.v1.open-data.monthly-csv'));
    $monthlyResponse->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertSee('period,verified_reports,appeals_filed,appeals_accepted');
});

test('open data web page renders inertia component with summary dataset', function (): void {
    $response = get(route('open-data'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('OpenData/Index')
            ->has('summary.metadata')
            ->has('summary.totals')
            ->has('summary.categories')
        );
});

test('transparency api returns historical and monthly reports', function (): void {
    $currentYear = (int) Carbon::now()->year;
    $currentMonth = (int) Carbon::now()->month;

    $historyResponse = getJson(route('api.v1.transparency.index'));
    $historyResponse->assertOk()
        ->assertJsonStructure([
            'reports' => [
                '*' => [
                    'period',
                    'year',
                    'month',
                    'submissions',
                    'outcomes',
                    'turnaround',
                    'appeals',
                ],
            ],
        ]);

    $singleResponse = getJson(route('api.v1.transparency.show', ['year' => $currentYear, 'month' => $currentMonth]));
    $singleResponse->assertOk()
        ->assertJsonPath('year', $currentYear)
        ->assertJsonPath('month', $currentMonth);
});

test('transparency web page renders inertia component', function (): void {
    $response = get(route('transparency'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Transparency/Index')
            ->has('currentReport')
            ->has('history')
        );
});

test('transparency report console command executes successfully', function (): void {
    artisan('reports:transparency-report')
        ->expectsOutputToContain('TRANSPARENCY REPORT')
        ->assertSuccessful();
});

test('retention cleanup command removes expired temporary uploads and stale spam', function (): void {
    Storage::fake('local');

    // Create fake temp share folder
    Storage::disk('local')->put('temp-shares/old-share/test.jpg', 'fake content');

    // Create stale spam report
    $staleSpam = Report::factory()->create([
        'workflow_status' => WorkflowStatus::Spam,
        'updated_at' => Carbon::now()->subDays(100),
    ]);

    artisan('reports:cleanup-retention --spam-days=90')
        ->expectsOutputToContain('Retention cleanup completed successfully.')
        ->assertSuccessful();

    expect(Report::find($staleSpam->id))->toBeNull();
});
