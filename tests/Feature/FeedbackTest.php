<?php

declare(strict_types=1);

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    RateLimiter::clear('feedback:ip:'.hash('sha256', '127.0.0.1'));
});

test('feedback web route renders inertia component', function (): void {
    $response = get(route('feedback'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Feedback/Index')
            ->has('types')
        );
});

test('user can submit a bug report with device info', function (): void {
    $response = postJson(route('api.v1.feedback.store'), [
        'type' => FeedbackType::BugReport->value,
        'title' => 'ম্যাপে জেলা পপআপ খুলছে না',
        'description' => 'মোবাইল ব্রাউজারে জেলা তালিকায় ক্লিক করলে বিস্তারিত লোড হয় না।',
        'page_url' => 'https://nomoresilence.org/districts',
        'contact_email' => 'user@example.com',
        'device_info' => [
            'screen' => '390x844',
            'browser' => 'Mobile Safari',
        ],
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure(['feedback_uuid', 'message']);

    $feedback = Feedback::where('uuid', $response->json('feedback_uuid'))->first();
    expect($feedback)->not->toBeNull()
        ->and($feedback->type)->toBe(FeedbackType::BugReport)
        ->and($feedback->status)->toBe(FeedbackStatus::Open)
        ->and($feedback->contact_email)->toBe('user@example.com')
        ->and($feedback->device_info['browser'])->toBe('Mobile Safari');
});

test('user can submit a feature request anonymously', function (): void {
    $response = postJson(route('api.v1.feedback.store'), [
        'type' => FeedbackType::FeatureRequest->value,
        'title' => 'নতুন অনুসন্ধান ফিল্টার যোগ করার অনুরোধ',
        'description' => 'জেলা ভিত্তিক ফিল্টারের সাথে ঘটনার তারিখ অনুযায়ী ফিল্টার করার ব্যবস্থা থাকলে ভালো হতো।',
    ]);

    $response->assertCreated()
        ->assertJson(['success' => true]);

    $feedback = Feedback::where('uuid', $response->json('feedback_uuid'))->first();
    expect($feedback)->not->toBeNull()
        ->and($feedback->type)->toBe(FeedbackType::FeatureRequest)
        ->and($feedback->contact_email)->toBeNull();
});

test('feedback validation rejects invalid input', function (): void {
    $response = postJson(route('api.v1.feedback.store'), [
        'type' => 'invalid_type',
        'title' => '',
        'description' => 'short',
        'contact_email' => 'not-an-email',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'title', 'description', 'contact_email']);
});

test('feedback submission enforces rate limiting', function (): void {
    $ipHash = hash('sha256', '127.0.0.1');
    $rateLimitKey = "feedback:ip:{$ipHash}";

    for ($i = 0; $i < 10; $i++) {
        RateLimiter::hit($rateLimitKey, 3600);
    }

    $response = postJson(route('api.v1.feedback.store'), [
        'type' => FeedbackType::General->value,
        'title' => 'সাধারণ মতামত',
        'description' => 'প্ল্যাটফর্মটির উদ্যোগ অত্যন্ত প্রশংসনীয়। ধন্যবাদ।',
    ]);

    $response->assertStatus(429);
});

test('admin can update feedback status and internal notes', function (): void {
    $admin = User::factory()->create();

    $feedback = Feedback::factory()->create([
        'status' => FeedbackStatus::Open,
        'admin_notes' => null,
    ]);

    $feedback->update([
        'status' => FeedbackStatus::Resolved,
        'admin_notes' => 'Fixed in commit 12345.',
    ]);

    expect($feedback->fresh()->status)->toBe(FeedbackStatus::Resolved)
        ->and($feedback->fresh()->admin_notes)->toBe('Fixed in commit 12345.');
});
