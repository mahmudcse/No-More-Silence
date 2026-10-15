<?php

declare(strict_types=1);

use Database\Seeders\DivisionsAndDistrictsSeeder;
use Database\Seeders\SoftLaunchSeeder;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

test('soft launch seeder populates verified incidents, profiles and public reports', function (): void {
    seed(DivisionsAndDistrictsSeeder::class);
    seed(SoftLaunchSeeder::class);

    assertDatabaseCount('incidents', 2);
    assertDatabaseCount('facebook_profiles', 2);
    assertDatabaseCount('reports', 2);

    assertDatabaseHas('reports', [
        'uuid' => '00000000-0000-4000-8000-000000000001',
        'workflow_status' => 'verified',
        'visibility' => 'public',
    ]);

    assertDatabaseHas('reports', [
        'uuid' => '00000000-0000-4000-8000-000000000002',
        'workflow_status' => 'verified',
        'visibility' => 'featured',
    ]);
});
