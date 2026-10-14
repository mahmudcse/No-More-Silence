<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    public function definition(): array
    {
        return [
            'division_id' => Division::factory(),
            'code' => fake()->unique()->numerify('####'),
            'name_en' => fake()->city(),
            'name_bn' => fake()->city(),
            'slug' => fake()->unique()->slug(),
            'population' => fake()->optional()->numberBetween(100000, 10000000),
        ];
    }
}
