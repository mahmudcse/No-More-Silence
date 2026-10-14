<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DivisionsAndDistrictsSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => config('seed.super_admin_email')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(config('seed.super_admin_password')),
            ],
        );

        $admin->assignRole('super_admin');
    }
}
