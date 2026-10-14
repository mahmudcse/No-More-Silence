<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportReview>
 */
class ReportReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'admin_id' => null,
            'from_status' => null,
            'to_status' => 'triaged',
            'checklist' => [],
            'note' => fake()->sentence(),
        ];
    }
}
