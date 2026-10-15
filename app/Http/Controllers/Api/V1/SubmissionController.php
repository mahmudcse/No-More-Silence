<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Reports\SubmitReport;
use App\Data\NewReportData;
use App\Enums\IncidentStatus;
use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitReportRequest;
use App\Models\Incident;
use App\Models\Report;
use App\Models\Reporter;
use App\Support\FacebookUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function store(SubmitReportRequest $request, SubmitReport $submitReport): JsonResponse
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $ipHash = hash_hmac('sha256', $ip, (string) config('app.key'));

        // Anti-abuse: Rate limit 10 submissions per day per IP-hash
        $rateLimitKey = "submission:ip:{$ipHash}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'আজকের দিনে আপনার আইপি থেকে সর্বোচ্চ জমার সীমা (১০টি) অতিক্রান্ত হয়েছে। অনুগ্রহ করে আগামীকাল চেষ্টা করুন।',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 86400);

        $submittedProfileUrl = (string) $request->input('submitted_profile_url');
        $submittedCommentUrl = $request->input('submitted_comment_url') ? (string) $request->input('submitted_comment_url') : null;
        $normalizedProfileUrl = FacebookUrlNormalizer::normalize($submittedProfileUrl) ?? $submittedProfileUrl;

        // Instant duplicate detection: same profile + comment url
        if ($submittedCommentUrl) {
            $existingReport = Report::query()
                ->where('submitted_comment_url', $submittedCommentUrl)
                ->first();

            if ($existingReport) {
                return response()->json([
                    'success' => true,
                    'already_reported' => true,
                    'message' => 'এই মন্তব্যটি ইতিমধ্যে আমাদের সিস্টেমে রিপোর্ট করা হয়েছে। তথ্যের নির্ভুলতা বৃদ্ধিতে সহযোগিতার জন্য ধন্যবাদ।',
                    'workflow_status' => $existingReport->workflow_status->value,
                ]);
            }
        }

        // Handle Incident linking or new Incident creation
        $incidentId = $request->filled('incident_id') ? (int) $request->input('incident_id') : null;
        if (! $incidentId && $request->filled('new_incident_title')) {
            $title = (string) $request->input('new_incident_title');
            $districtId = $request->filled('new_incident_district_id') ? (int) $request->input('new_incident_district_id') : null;
            $slug = Str::slug($title);
            if ($slug === '') {
                $slug = 'incident-'.Str::random(8);
            } else {
                $slug .= '-'.Str::random(5);
            }

            $incident = Incident::create([
                'title_bn' => $title,
                'title_en' => $title,
                'slug' => $slug,
                'district_id' => $districtId,
                'status' => IncidentStatus::Draft,
            ]);
            $incidentId = $incident->id;
        }

        // Handle optional Reporter
        $reporterId = null;
        if ($request->filled('reporter_email')) {
            $reporter = Reporter::query()->firstOrCreate(
                ['email' => (string) $request->input('reporter_email')],
                ['reputation' => 0, 'is_banned' => false]
            );
            $reporterId = $reporter->id;
        }

        $category = null;
        if ($request->filled('category')) {
            $category = ReportCategory::tryFrom((string) $request->input('category'));
        }

        $submittedReport = $submitReport->handle(new NewReportData(
            submittedProfileUrl: $normalizedProfileUrl,
            submittedCommentUrl: $submittedCommentUrl,
            commentText: $request->input('comment_text') ? (string) $request->input('comment_text') : null,
            category: $category,
            reporterId: $reporterId,
            incidentId: $incidentId,
            platform: Platform::Facebook,
            submittedVia: SubmittedVia::Web,
            submitterIpHash: $ipHash,
        ));

        $report = $submittedReport->report;

        // Attach uploaded screenshot evidence by section with custom properties
        /** @var array<string, array<mixed>> $evidenceSections */
        $evidenceSections = [
            'post' => (array) $request->file('evidence_post', []),
            'comment' => array_merge(
                (array) $request->file('evidence_comment', []),
                (array) $request->file('evidence', []) // legacy fallback
            ),
            'profile' => (array) $request->file('evidence_profile', []),
            'other' => (array) $request->file('evidence_other', []),
        ];

        foreach ($evidenceSections as $type => $files) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $report->addMedia($file)
                        ->withCustomProperties([
                            'evidence_type' => $type,
                        ])
                        ->toMediaCollection('original');
                }
            }
        }

        return response()->json([
            'success' => true,
            'already_reported' => false,
            'tracking_code' => $submittedReport->trackingCode,
            'uuid' => $report->uuid,
            'message' => 'আপনার রিপোর্টটি সফলভাবে নথিবদ্ধ হয়েছে। স্ট্যাটাস জানতে ট্র্যাকিং কোডটি সংরক্ষণ করুন।',
        ], 201);
    }

    public function track(string $code): JsonResponse
    {
        $code = trim($code);
        $codeHash = hash('sha256', $code);

        $report = Report::query()
            ->where('tracking_code_hash', $codeHash)
            ->with(['incident'])
            ->first();

        if (! $report) {
            return response()->json([
                'success' => false,
                'message' => 'প্রদত্ত ট্র্যাকিং কোডের বিপরীতে কোনো রিপোর্ট খুঁজে পাওয়া যায়নি। কোডটি পুনরায় যাচাই করুন।',
            ], 404);
        }

        $statusLabelsBn = [
            'submitted' => 'জমা হয়েছে (পর্যালোচনার অপেক্ষায়)',
            'triaged' => 'প্রাথমিক বাছাই সম্পন্ন',
            'in_verification' => '৮-দফা যাচাইকরণ চলছে',
            'verified' => 'যাচাইকৃত (প্রকাশের অপেক্ষায়)',
            'needs_info' => 'আরও প্রমাণের প্রয়োজন',
            'duplicate' => 'অনুরূপ রেকর্ড পূর্বে বিদ্যমান',
            'rejected' => 'যাচাইয়ে অগ্রহণযোগ্য',
            'retracted' => 'আপিলের পর প্রত্যাহারকৃত',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'tracking_code' => $code,
                'uuid' => $report->uuid,
                'status' => $report->workflow_status->value,
                'status_label_bn' => $statusLabelsBn[$report->workflow_status->value] ?? $report->workflow_status->label(),
                'status_label_en' => $report->workflow_status->label(),
                'visibility' => $report->visibility->value,
                'category' => $report->category?->value,
                'category_label_bn' => $report->category?->labelBn(),
                'submitted_at' => $report->created_at?->toIso8601String(),
                'verified_at' => $report->verified_at?->toIso8601String(),
                'incident' => $report->incident ? [
                    'title_bn' => $report->incident->title_bn,
                    'title_en' => $report->incident->title_en,
                ] : null,
            ],
        ]);
    }
}
