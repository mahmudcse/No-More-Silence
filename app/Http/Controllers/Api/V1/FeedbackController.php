<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Store a new user feedback, bug report, or feature suggestion.
     */
    public function store(Request $request): JsonResponse
    {
        $ipHash = hash('sha256', (string) $request->ip());
        $rateLimitKey = "feedback:ip:{$ipHash}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'অতিরিক্ত অনুরোধ করা হয়েছে। অনুগ্রহ করে কিছুক্ষণ পর পুনরায় চেষ্টা করুন। (Too many attempts. Please try again later.)',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600);

        $validated = $request->validate([
            'type' => ['required', Rule::enum(FeedbackType::class)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'page_url' => ['nullable', 'url', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'device_info' => ['nullable', 'array'],
        ]);

        $feedback = Feedback::create([
            'type' => FeedbackType::from((string) $validated['type']),
            'title' => (string) $validated['title'],
            'description' => (string) $validated['description'],
            'page_url' => isset($validated['page_url']) ? (string) $validated['page_url'] : null,
            'contact_email' => isset($validated['contact_email']) ? (string) $validated['contact_email'] : null,
            'device_info' => $validated['device_info'] ?? null,
            'status' => FeedbackStatus::Open,
            'ip_hash' => $ipHash,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'আপনার মূল্যবান মতামত ও তথ্য সফলভাবে গৃহীত হয়েছে। প্ল্যাটফর্মটির মানোন্নয়নে সহযোগিতার জন্য ধন্যবাদ।',
            'feedback_uuid' => $feedback->uuid,
        ], 201);
    }
}
