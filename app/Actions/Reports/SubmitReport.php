<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Data\NewReportData;
use App\Data\SubmittedReport;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Events\ReportSubmitted;
use App\Models\Report;
use Illuminate\Support\Str;

final class SubmitReport
{
    public function handle(NewReportData $data): SubmittedReport
    {
        $trackingCode = Str::random(32);

        $report = Report::create([
            'tracking_code_hash' => hash('sha256', $trackingCode),
            'reporter_id' => $data->reporterId,
            'facebook_profile_id' => $data->facebookProfileId,
            'incident_id' => $data->incidentId,
            'submitted_profile_url' => $data->submittedProfileUrl,
            'submitted_comment_url' => $data->submittedCommentUrl,
            'comment_text' => $data->commentText,
            'comment_text_translation' => $data->commentTextTranslation,
            'comment_posted_at' => $data->commentPostedAt,
            'category' => $data->category,
            'severity' => $data->severity,
            'workflow_status' => WorkflowStatus::Submitted,
            'visibility' => Visibility::Hidden,
            'platform' => $data->platform,
            'submitted_via' => $data->submittedVia,
            'submitter_ip_hash' => $data->submitterIpHash,
        ]);

        ReportSubmitted::dispatch($report);

        return new SubmittedReport($report, $trackingCode);
    }
}
