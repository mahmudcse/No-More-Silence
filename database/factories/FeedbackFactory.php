<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(FeedbackType::cases()),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'page_url' => fake()->url(),
            'contact_email' => fake()->safeEmail(),
            'device_info' => [
                'user_agent' => fake()->userAgent(),
                'screen' => '1920x1080',
            ],
            'status' => FeedbackStatus::Open,
            'admin_notes' => null,
            'ip_hash' => hash('sha256', fake()->ipv4()),
        ];
    }
}
