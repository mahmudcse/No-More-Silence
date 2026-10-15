<?php

declare(strict_types=1);

use App\Filament\Widgets\VisitorChartWidget;
use App\Filament\Widgets\VisitorStatsOverviewWidget;
use App\Models\DailyVisitStat;
use App\Models\User;
use App\Services\VisitTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Role::findOrCreate('super_admin');

    // Clean up test keys in Redis
    Redis::del([
        'visits:active',
        'visits:pv:'.today()->toDateString(),
        'visits:uv:'.today()->toDateString(),
        'visits:pages:'.today()->toDateString(),
    ]);
});

test('middleware tracks normal visitors but ignores bots and excluded paths', function (): void {
    $tracker = app(VisitTrackerService::class);

    // Initial state
    expect($tracker->getTodayPageviews())->toBe(0);

    // 1. Legitimate user visit to homepage
    get('/', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0'])
        ->assertOk();

    expect($tracker->getTodayPageviews())->toBe(1)
        ->and($tracker->getTodayUniques())->toBe(1)
        ->and($tracker->getActiveVisitorsCount())->toBe(1);

    // 2. Second visit from same user to another page
    get('/search', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0'])
        ->assertOk();

    // Pageviews should be 2, but unique visitors stays 1
    expect($tracker->getTodayPageviews())->toBe(2)
        ->and($tracker->getTodayUniques())->toBe(1);

    // 3. Search engine crawler / bot visit should NOT be counted
    get('/', ['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
        ->assertOk();

    expect($tracker->getTodayPageviews())->toBe(2);

    // 4. Admin routes should not be tracked
    $admin = User::factory()->withMfa()->create()->assignRole('super_admin');
    actingAs($admin)->get('/admin')->assertOk();

    expect($tracker->getTodayPageviews())->toBe(2);
});

test('aggregate console command flushes redis stats into daily_visit_stats table', function (): void {
    $tracker = app(VisitTrackerService::class);

    $req = Request::create('/', 'GET', [], [], [], [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Safari/537.36',
    ]);
    $tracker->recordVisit($req);

    expect($tracker->getTodayPageviews())->toBe(1);

    artisan('stats:aggregate-visits', ['--date' => today()->toDateString()])
        ->assertSuccessful();

    $record = DailyVisitStat::whereDate('date', today())->first();
    expect($record)->not->toBeNull()
        ->and($record->pageviews)->toBe(1)
        ->and($record->unique_visitors)->toBe(1);
});

test('weekly trend combines historical records and today live stats', function (): void {
    DailyVisitStat::factory()->create([
        'date' => today()->subDays(2),
        'pageviews' => 1250,
        'unique_visitors' => 840,
    ]);

    $tracker = app(VisitTrackerService::class);
    $trend = $tracker->getWeeklyTrend();

    expect($trend['labels'])->toHaveCount(7)
        ->and($trend['pageviews'])->toHaveCount(7)
        ->and($trend['uniques'])->toHaveCount(7);
});

test('filament widgets mount without errors', function (): void {
    $admin = User::factory()->withMfa()->create()->assignRole('super_admin');
    actingAs($admin);

    Livewire::test(VisitorStatsOverviewWidget::class)
        ->assertSuccessful();

    Livewire::test(VisitorChartWidget::class)
        ->assertSuccessful();
});
