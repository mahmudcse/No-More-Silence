<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('horizon is accessible in local environment', function () {
    app()->detectEnvironment(fn () => 'local');

    get('/horizon')
        ->assertOk();
});

test('horizon rejects guests in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    get('/horizon')
        ->assertForbidden();
});

test('horizon rejects regular users in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    $user = User::factory()->create();

    actingAs($user)
        ->get('/horizon')
        ->assertForbidden();
});

test('horizon allows super admin in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    Role::findOrCreate('super_admin');
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    actingAs($admin)
        ->get('/horizon')
        ->assertOk();
});
