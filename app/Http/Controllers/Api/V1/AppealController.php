<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitAppealRequest;
use App\Models\Appeal;
use App\Models\FacebookProfile;
use App\Models\ProfileAlias;
use App\Models\Report;
use App\Support\FacebookUrlNormalizer;
use Illuminate\Http\JsonResponse;

class AppealController extends Controller
{
    public function store(SubmitAppealRequest $request): JsonResponse
    {
        $profileUrl = (string) $request->input('facebook_profile_url');
        $normalizedUrl = FacebookUrlNormalizer::normalize($profileUrl) ?? $profileUrl;

        $profile = FacebookProfile::query()
            ->where('canonical_url', $normalizedUrl)
            ->first();

        if (! $profile) {
            $alias = ProfileAlias::query()->where('alias_url', $normalizedUrl)->first();
            if ($alias) {
                $profile = $alias->facebookProfile;
            }
        }

        $reportId = null;
        if ($request->filled('report_uuid')) {
            $report = Report::query()->where('uuid', (string) $request->input('report_uuid'))->first();
            $reportId = $report?->id;
        }

        $appeal = Appeal::create([
            'facebook_profile_id' => $profile?->id,
            'report_id' => $reportId,
            'contact' => (string) $request->input('contact'),
            'message' => (string) $request->input('message'),
            'ownership_proof' => $request->input('ownership_proof') ? (string) $request->input('ownership_proof') : null,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'appeal_id' => $appeal->id,
            'message' => 'আপনার আপিলটি নিরপেক্ষ পর্যালোচনার জন্য গৃহীত হয়েছে। যাচাই শেষে প্রদত্ত যোগাযোগের ঠিকানায় ফলাফল জানানো হবে।',
        ], 201);
    }
}
