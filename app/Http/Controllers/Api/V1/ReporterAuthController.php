<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Reporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\PersonalAccessToken;

class ReporterAuthController extends Controller
{
    public function magicLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = (string) $request->input('email');
        $reporter = Reporter::query()->firstOrCreate(
            ['email' => $email],
            ['reputation' => 0, 'is_banned' => false]
        );

        $verifyUrl = URL::temporarySignedRoute(
            'api.v1.auth.verify',
            now()->addMinutes(30),
            ['id' => $reporter->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'ম্যাজিক লগইন লিঙ্ক তৈরি হয়েছে। আপনার ইমেইল চেক করুন।',
            'verify_url' => $verifyUrl,
        ]);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'এই যাচাইকরণ লিঙ্কটির মেয়াদ শেষ হয়ে গেছে অথবা এটি অবৈধ।',
            ], 401);
        }

        $reporter = Reporter::query()->findOrFail($id);
        $token = $reporter->createToken('reporter_token')->plainTextToken;

        session(['reporter_id' => $reporter->id]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'reporter' => [
                'id' => $reporter->id,
                'email' => $reporter->email,
                'reputation' => $reporter->reputation,
            ],
            'message' => 'সফলভাবে প্রমাণীকরণ সম্পন্ন হয়েছে।',
        ]);
    }

    public function submissions(Request $request): JsonResponse
    {
        $reporter = null;

        $bearer = $request->bearerToken();
        if ($bearer) {
            $tokenModel = PersonalAccessToken::findToken($bearer);
            if ($tokenModel && $tokenModel->tokenable instanceof Reporter) {
                $reporter = $tokenModel->tokenable;
            }
        }

        if (! $reporter && session()->has('reporter_id')) {
            $reporter = Reporter::query()->find(session('reporter_id'));
        }

        if (! $reporter) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে লগইন করুন।',
            ], 401);
        }

        $reports = Report::query()
            ->where('reporter_id', $reporter->id)
            ->with(['incident.district'])
            ->latest()
            ->get();

        $data = $reports->map(fn (Report $report): array => [
            'uuid' => $report->uuid,
            'workflow_status' => $report->workflow_status->value,
            'workflow_status_label' => $report->workflow_status->label(),
            'visibility' => $report->visibility->value,
            'category' => $report->category?->value,
            'category_label_bn' => $report->category?->labelBn(),
            'comment_text' => $report->comment_text,
            'submitted_at' => $report->created_at?->toIso8601String(),
            'verified_at' => $report->verified_at?->toIso8601String(),
            'incident' => $report->incident ? [
                'title_bn' => $report->incident->title_bn,
                'title_en' => $report->incident->title_en,
            ] : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
