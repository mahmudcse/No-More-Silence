<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AliasType;
use App\Models\FacebookProfile;
use App\Models\ProfileAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfileAlias>
 */
class ProfileAliasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'facebook_profile_id' => FacebookProfile::factory(),
            'type' => fake()->randomElement(AliasType::cases()),
            'value' => fake()->userName(),
            'value_normalized' => fake()->userName(),
            'seen_at' => now(),
        ];
    }
}
