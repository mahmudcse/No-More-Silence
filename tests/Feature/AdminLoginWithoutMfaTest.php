<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('admin login page renders without two-factor prompt', function (): void {
    $response = get('/admin/login');

    $response->assertOk();
    $response->assertDontSee('Two-factor');
    $response->assertDontSee('Authentication code');
});

test('admin can access dashboard directly without MFA setup or challenge', function (): void {
    Role::firstOrCreate(['name' => 'super_admin']);
    $user = User::factory()->create([
        'email' => 'admin@test.local',
    ]);
    $user->assignRole('super_admin');

    $response = actingAs($user)->get('/admin');

    $response->assertOk();
    // Ensures no redirect to MFA setup or verification route
    $response->assertDontSee('app-authentication');
});

test('filament admin panel has multi-factor authentication disabled', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->hasMultiFactorAuthentication())->toBeFalse();
});
