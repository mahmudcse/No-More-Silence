<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AppealStatus;
use App\Models\Appeal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appeal>
 */
class AppealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contact' => fake()->safeEmail(),
            'ownership_proof' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'status' => AppealStatus::Open,
        ];
    }
}
