<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StatsSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatsSnapshot>
 */
class StatsSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'district_id' => null,
            'received' => 0,
            'verified' => 0,
            'public' => 0,
            'profiles' => 0,
        ];
    }
}
