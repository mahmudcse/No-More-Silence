<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DistrictSource;
use App\Enums\Platform;
use App\Enums\ProfileStatus;
use App\Models\FacebookProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FacebookProfile>
 */
class FacebookProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fb_numeric_id' => fake()->unique()->optional()->numerify('1000###########'),
            'canonical_url' => 'https://www.facebook.com/'.fake()->unique()->userName(),
            'current_display_name' => fake()->name(),
            'display_name_normalized' => fake()->unique()->userName(),
            'district_source' => DistrictSource::Unknown,
            'profile_status' => ProfileStatus::Active,
            'platform' => Platform::Facebook,
            'public_reports_count' => 0,
        ];
    }
}
