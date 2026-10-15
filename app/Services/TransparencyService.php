<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppealStatus;
use App\Enums\WorkflowStatus;
use App\Models\Appeal;
use App\Models\Report;
use App\Models\ReportReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TransparencyService
{
    /**
     * Get transparency metrics for a specific month.
     *
     * @return array{
     *     period: string,
     *     year: int,
     *     month: int,
     *     submissions: array{
     *         total: int,
     *         anonymous: int,
     *         authenticated: int,
     *         web_share_target: int
     *     },
     *     outcomes: array{
     *         verified: int,
     *         rejected: int,
     *         duplicate: int,
     *         spam: int,
     *         retracted: int
     *     },
     *     turnaround: array{
     *         avg_verification_hours: float,
     *         total_reviews_logged: int
     *     },
     *     appeals: array{
     *         filed: int,
     *         accepted: int,
     *         rejected: int,
     *         pending: int,
     *         right_of_reply_published: int
     *     }
     * }
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $cacheKey = "transparency_report_{$year}_{$month}";

        return Cache::remember($cacheKey, 1800, function () use ($year, $month): array {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            // Submissions received
            $totalSubmissions = Report::query()->whereBetween('created_at', [$start, $end])->count();
            $anonymousSubmissions = Report::query()->whereBetween('created_at', [$start, $end])->whereNull('reporter_id')->count();
            $authenticatedSubmissions = $totalSubmissions - $anonymousSubmissions;
            $shareTargetSubmissions = Report::query()->whereBetween('created_at', [$start, $end])->where('submitted_via', 'web')->count();

            // Outcomes
            $verifiedReports = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereBetween('verified_at', [$start, $end])
                ->count();

            $rejectedReports = Report::query()
                ->where('workflow_status', WorkflowStatus::Rejected)
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $duplicateReports = Report::query()
                ->where('workflow_status', WorkflowStatus::Duplicate)
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $spamReports = Report::query()
                ->where('workflow_status', WorkflowStatus::Spam)
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $retractedReports = Report::query()
                ->where('workflow_status', WorkflowStatus::Retracted)
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            // Reviews and turnaround calculation
            $reviewsLogged = ReportReview::query()->whereBetween('created_at', [$start, $end])->count();

            $verifiedSample = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereBetween('verified_at', [$start, $end])
                ->whereNotNull('created_at')
                ->whereNotNull('verified_at')
                ->limit(200)
                ->get();

            $totalHours = 0.0;
            $sampleCount = 0;
            foreach ($verifiedSample as $rep) {
                if ($rep->created_at !== null && $rep->verified_at !== null) {
                    $hours = $rep->created_at->diffInMinutes($rep->verified_at) / 60.0;
                    $totalHours += $hours;
                    $sampleCount++;
                }
            }
            $avgHours = $sampleCount > 0 ? round($totalHours / $sampleCount, 1) : 0.0;

            // Appeals
            $appealsFiled = Appeal::query()->whereBetween('created_at', [$start, $end])->count();
            $appealsAccepted = Appeal::query()->where('status', AppealStatus::Accepted)->whereBetween('updated_at', [$start, $end])->count();
            $appealsRejected = Appeal::query()->where('status', AppealStatus::Rejected)->whereBetween('updated_at', [$start, $end])->count();
            $appealsPending = Appeal::query()->where('status', AppealStatus::Open)->whereBetween('created_at', [$start, $end])->count();
            $rightOfReplyCount = Appeal::query()
                ->whereNotNull('right_of_reply_text')
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            return [
                'period' => $start->format('F Y'),
                'year' => $year,
                'month' => $month,
                'submissions' => [
                    'total' => $totalSubmissions,
                    'anonymous' => $anonymousSubmissions,
                    'authenticated' => $authenticatedSubmissions,
                    'web_share_target' => $shareTargetSubmissions,
                ],
                'outcomes' => [
                    'verified' => $verifiedReports,
                    'rejected' => $rejectedReports,
                    'duplicate' => $duplicateReports,
                    'spam' => $spamReports,
                    'retracted' => $retractedReports,
                ],
                'turnaround' => [
                    'avg_verification_hours' => $avgHours,
                    'total_reviews_logged' => $reviewsLogged,
                ],
                'appeals' => [
                    'filed' => $appealsFiled,
                    'accepted' => $appealsAccepted,
                    'rejected' => $appealsRejected,
                    'pending' => $appealsPending,
                    'right_of_reply_published' => $rightOfReplyCount,
                ],
            ];
        });
    }

    /**
     * Get historical transparency series for recent months.
     *
     * @return list<array{
     *     period: string,
     *     year: int,
     *     month: int,
     *     submissions: array{
     *         total: int,
     *         anonymous: int,
     *         authenticated: int,
     *         web_share_target: int
     *     },
     *     outcomes: array{
     *         verified: int,
     *         rejected: int,
     *         duplicate: int,
     *         spam: int,
     *         retracted: int
     *     },
     *     turnaround: array{
     *         avg_verification_hours: float,
     *         total_reviews_logged: int
     *     },
     *     appeals: array{
     *         filed: int,
     *         accepted: int,
     *         rejected: int,
     *         pending: int,
     *         right_of_reply_published: int
     *     }
     * }>
     */
    public function getHistoricalReports(int $months = 6): array
    {
        $reports = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $year = (int) $cursor->year;
            $month = (int) $cursor->month;
            $reports[] = $this->getMonthlyReport($year, $month);
            $cursor->subMonth();
        }

        return $reports;
    }
}
