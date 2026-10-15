<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Role::findOrCreate('super_admin');
    Role::findOrCreate('verifier');
});

test('super admin can access admins list page', function (): void {
    $admin = User::factory()->withMfa()->create();
    $admin->assignRole('super_admin');

    actingAs($admin)
        ->get('/admin/users')
        ->assertOk();
});

test('super admin can create a new admin with roles', function (): void {
    $admin = User::factory()->withMfa()->create();
    $admin->assignRole('super_admin');

    actingAs($admin);

    $role = Role::findOrCreate('verifier');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New Moderator',
            'email' => 'moderator@example.com',
            'password' => 'secret1234',
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newUser = User::where('email', 'moderator@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole('verifier'))->toBeTrue();
});

test('admin cannot delete own account through policy', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    expect($admin->can('delete', $admin))->toBeFalse();

    $otherAdmin = User::factory()->create();
    expect($admin->can('delete', $otherAdmin))->toBeTrue();
});
