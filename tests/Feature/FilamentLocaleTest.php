<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

test('filament admin panel defaults to english even when app locale is bangla', function (): void {
    // Verify base app config is bn as per .env
    config(['app.locale' => 'bn']);

    $response = get('/admin/login');

    $response->assertOk();
    // English login strings should be present
    $response->assertSee('Sign in');
    expect(app()->getLocale())->toBe('en');
});

test('admin can switch locale to bangla and it updates session and locale', function (): void {
    $switchResponse = get(route('filament.admin.locale.switch', ['locale' => 'bn']));

    $switchResponse->assertRedirect();
    $switchResponse->assertSessionHas('filament_locale', 'bn');

    // Follow-up request with session has bangla locale
    $loginResponse = withSession(['filament_locale' => 'bn'])->get('/admin/login');

    $loginResponse->assertOk();
    expect(app()->getLocale())->toBe('bn');
});

test('admin can switch locale back to english', function (): void {
    $switchResponse = withSession(['filament_locale' => 'bn'])
        ->get(route('filament.admin.locale.switch', ['locale' => 'en']));

    $switchResponse->assertRedirect();
    $switchResponse->assertSessionHas('filament_locale', 'en');

    $loginResponse = withSession(['filament_locale' => 'en'])->get('/admin/login');

    $loginResponse->assertOk();
    expect(app()->getLocale())->toBe('en');
});

test('unsupported locale is ignored and defaults to english', function (): void {
    $switchResponse = get(route('filament.admin.locale.switch', ['locale' => 'fr']));

    $switchResponse->assertRedirect();
    $switchResponse->assertSessionMissing('filament_locale');

    $response = get('/admin/login');
    $response->assertOk();
    expect(app()->getLocale())->toBe('en');
});

test('authenticated admin sees language switcher in admin panel', function (): void {
    Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->withMfa()->create();
    $user->assignRole('super_admin');

    $response = actingAs($user)->get('/admin');

    $response->assertOk();
    $response->assertSee(route('filament.admin.locale.switch', ['locale' => 'bn']));
    $response->assertSee(route('filament.admin.locale.switch', ['locale' => 'en']));
});
