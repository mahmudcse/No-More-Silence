<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FacebookProfile;
use App\Models\ProfileSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileSnapshot>
 */
class ProfileSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'facebook_profile_id' => FacebookProfile::factory(),
            'display_name' => fake()->name(),
            'captured_at' => now(),
        ];
    }
}
