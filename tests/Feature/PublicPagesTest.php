<?php

declare(strict_types=1);

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\Division;
use App\Models\FacebookProfile;
use App\Models\Incident;
use App\Models\Report;

use function Pest\Laravel\get;

function createPublicTestDistrict(): District
{
    $division = Division::factory()->create(['name_en' => 'Dhaka', 'name_bn' => 'ঢাকা']);

    return District::factory()->create([
        'division_id' => $division->id,
        'name_en' => 'Dhaka',
        'name_bn' => 'ঢাকা',
        'slug' => 'dhaka',
        'population' => 14734025,
    ]);
}

test('welcome page renders successfully with headline stats and featured reports', function (): void {
    $district = createPublicTestDistrict();
    $profile = FacebookProfile::factory()->create(['current_display_name' => 'John Doe', 'public_reports_count' => 1]);
    $incident = Incident::factory()->create(['district_id' => $district->id, 'title_bn' => 'টেস্ট ঘটনা']);

    Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'incident_id' => $incident->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'comment_text' => 'পাবলিক মন্তব্য',
        'published_at' => now(),
    ]);

    get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('nationalStats')
            ->has('districtStats')
            ->has('featuredReports')
        );
});

test('search page renders and displays same-name warning banner for name queries', function (): void {
    FacebookProfile::factory()->create([
        'current_display_name' => 'Rahim Ahmed',
        'display_name_normalized' => 'rahim ahmed',
        'public_reports_count' => 1,
    ]);

    get(route('search', ['q' => 'Rahim']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Search')
            ->where('initialQuery', 'Rahim')
            ->where('searchType', 'name')
            ->whereNot('warning', null)
            ->has('results', 1)
        );
});

test('search page supports exact facebook url search', function (): void {
    FacebookProfile::factory()->create([
        'canonical_url' => 'https://facebook.com/profile.php?id=1000888999',
        'public_reports_count' => 1,
    ]);

    get(route('search', ['q' => 'https://m.facebook.com/profile.php?id=1000888999&ref=share']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Search')
            ->where('searchType', 'url')
            ->has('results', 1)
        );
});

test('profile page displays profile details and verified comments for documented profiles', function (): void {
    $profile = FacebookProfile::factory()->create([
        'current_display_name' => 'Documented User',
        'public_reports_count' => 1,
    ]);

    Report::factory()->create([
        'facebook_profile_id' => $profile->id,
        'workflow_status' => WorkflowStatus::Verified,
        'visibility' => Visibility::Public,
        'comment_text' => 'যাচাইকৃত মন্তব্য',
        'published_at' => now(),
    ]);

    get(route('profiles.show', ['uuid' => $profile->uuid]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Profiles/Show')
            ->where('profile.uuid', $profile->uuid)
            ->where('profile.display_name', 'Documented User')
        );
});

test('profile page returns 404 for non-existent profile or profile with zero public reports', function (): void {
    $profile = FacebookProfile::factory()->create([
        'public_reports_count' => 0,
    ]);

    get(route('profiles.show', ['uuid' => $profile->uuid]))
        ->assertNotFound();

    get(route('profiles.show', ['uuid' => 'non-existent-uuid']))
        ->assertNotFound();
});

test('incidents index and show pages render successfully', function (): void {
    $district = createPublicTestDistrict();
    $incident = Incident::factory()->create([
        'district_id' => $district->id,
        'slug' => 'test-incident-slug',
        'title_bn' => 'ঘটনা শিরোনাম',
    ]);

    get(route('incidents.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Incidents/Index')
            ->has('incidents.data')
        );

    get(route('incidents.show', ['slug' => $incident->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Incidents/Show')
            ->where('incident.slug', $incident->slug)
        );
});

test('districts index and show pages render successfully', function (): void {
    $district = createPublicTestDistrict();

    get(route('districts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Districts/Index')
            ->has('districts')
            ->has('incidentStats')
        );

    get(route('districts.show', ['slug' => $district->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Districts/Show')
            ->where('district.slug', $district->slug)
        );
});

test('all static policy and informational pages render successfully', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pages/About'));

    get(route('methodology'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pages/Methodology'));

    get(route('safety-resources'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pages/SafetyResources'));

    get(route('terms'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pages/Terms'));

    get(route('privacy'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Pages/Privacy'));
});

test('phase 3 public pages render successfully', function (): void {
    get(route('submit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Submissions/Create')
            ->has('categories')
            ->has('districts')
            ->has('incidents')
        );

    get(route('track'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Submissions/Track'));

    get(route('appeals.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Appeals/Create'));

    get(route('reporter.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Reporter/Dashboard'));
});
