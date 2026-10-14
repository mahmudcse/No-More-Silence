<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title_en' => fake()->sentence(6),
            'title_bn' => fake()->sentence(6),
            'slug' => fake()->unique()->slug(),
            'occurred_on' => fake()->date(),
            'summary' => fake()->paragraph(),
            'source_urls' => [fake()->url()],
            'status' => IncidentStatus::Draft,
        ];
    }
}
