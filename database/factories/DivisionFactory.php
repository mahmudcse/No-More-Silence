<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Division>
 */
class DivisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('##'),
            'name_en' => fake()->city(),
            'name_bn' => fake()->city(),
        ];
    }
}
