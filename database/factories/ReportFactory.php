<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submitted_profile_url' => 'https://www.facebook.com/'.fake()->userName(),
            'submitted_comment_url' => 'https://www.facebook.com/'.fake()->userName().'/posts/'.fake()->randomNumber(9),
            'comment_text' => fake()->paragraph(),
            'category' => fake()->randomElement(ReportCategory::cases()),
            'severity' => fake()->numberBetween(1, 3),
            'workflow_status' => WorkflowStatus::Submitted,
            'visibility' => Visibility::Hidden,
            'is_hidden_from_homepage' => false,
            'platform' => Platform::Facebook,
            'submitted_via' => SubmittedVia::Web,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_status' => WorkflowStatus::Verified,
            'verified_at' => now(),
        ]);
    }

    public function public(): static
    {
        return $this->verified()->state(fn (array $attributes) => [
            'visibility' => Visibility::Public,
            'published_at' => now(),
        ]);
    }

    public function hiddenFromHomepage(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden_from_homepage' => true,
        ]);
    }
}
