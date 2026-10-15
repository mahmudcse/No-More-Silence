<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DailyVisitStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyVisitStat>
 */
class DailyVisitStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'pageviews' => fake()->numberBetween(100, 5000),
            'unique_visitors' => fake()->numberBetween(50, 2500),
            'top_pages' => [
                '/' => fake()->numberBetween(50, 2000),
                '/submit' => fake()->numberBetween(10, 500),
                '/search' => fake()->numberBetween(5, 300),
            ],
        ];
    }
}
