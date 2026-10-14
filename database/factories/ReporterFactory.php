<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reporter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reporter>
 */
class ReporterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'display_handle' => fake()->userName(),
            'reputation' => 0,
            'is_banned' => false,
        ];
    }
}
