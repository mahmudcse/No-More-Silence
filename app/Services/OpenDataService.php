<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppealStatus;
use App\Enums\ReportCategory;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Appeal;
use App\Models\District;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class OpenDataService
{
    /**
     * Get aggregated, fully anonymized open dataset summary for research and policy analysis.
     *
     * @return array{
     *     metadata: array{
     *         title: string,
     *         license: string,
     *         legal_compliance: string,
     *         generated_at: string
     *     },
     *     totals: array{
     *         verified_reports: int,
     *         incidents_covered: int,
     *         districts_affected: int,
     *         appeals_processed: int
     *     },
     *     categories: list<array{
     *         key: string,
     *         label_en: string,
     *         label_bn: string,
     *         count: int,
     *         percentage: float
     *     }>,
     *     districts: list<array{
     *         bbs_code: string,
     *         name_en: string,
     *         name_bn: string,
     *         division_en: string,
     *         population: int,
     *         verified_reports: int,
     *         rate_per_100k: float
     *     }>,
     *     monthly_trends: list<array{
     *         period: string,
     *         verified_reports: int,
     *         appeals_filed: int,
     *         appeals_accepted: int
     *     }>,
     *     appeals_resolution: array{
     *         total: int,
     *         accepted: int,
     *         rejected: int,
     *         pending: int,
     *         acceptance_rate: float
     *     }
     * }
     */
    public function getSummary(): array
    {
        return Cache::remember('open_data_summary_v1', 3600, function (): array {
            $verifiedReportsCount = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                ->count();

            $incidentsCount = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                ->whereNotNull('incident_id')
                ->distinct('incident_id')
                ->count('incident_id');

            // Category breakdown
            $categoriesBreakdown = [];
            foreach (ReportCategory::cases() as $cat) {
                $count = Report::query()
                    ->where('workflow_status', WorkflowStatus::Verified)
                    ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                    ->where('category', $cat)
                    ->count();

                $categoriesBreakdown[] = [
                    'key' => $cat->value,
                    'label_en' => $cat->labelEn(),
                    'label_bn' => $cat->labelBn(),
                    'count' => $count,
                    'percentage' => $verifiedReportsCount > 0 ? round(($count / $verifiedReportsCount) * 100, 1) : 0.0,
                ];
            }

            // District breakdown
            $districtsData = [];
            $districtsAffected = 0;
            $districts = District::with('division')->orderBy('name_en')->get();

            foreach ($districts as $district) {
                $reportCount = Report::query()
                    ->where('workflow_status', WorkflowStatus::Verified)
                    ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                    ->whereHas('incident', function ($q) use ($district): void {
                        $q->where('district_id', $district->id);
                    })
                    ->count();

                if ($reportCount > 0) {
                    $districtsAffected++;
                }

                $ratePer100k = $district->population > 0
                    ? round(($reportCount / $district->population) * 100000, 2)
                    : 0.0;

                $districtsData[] = [
                    'bbs_code' => (string) $district->code,
                    'name_en' => $district->name_en,
                    'name_bn' => $district->name_bn,
                    'division_en' => $district->division->name_en ?? 'Unknown',
                    'population' => (int) $district->population,
                    'verified_reports' => $reportCount,
                    'rate_per_100k' => $ratePer100k,
                ];
            }

            // Monthly trends (past 12 months)
            $monthlyTrends = [];
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $currentPeriod = $startDate->copy();

            while ($currentPeriod->lessThanOrEqualTo(Carbon::now()->endOfMonth())) {
                $periodKey = $currentPeriod->format('Y-m');
                $start = $currentPeriod->copy()->startOfMonth();
                $end = $currentPeriod->copy()->endOfMonth();

                $monthVerified = Report::query()
                    ->where('workflow_status', WorkflowStatus::Verified)
                    ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                    ->whereBetween('verified_at', [$start, $end])
                    ->count();

                $monthAppealsFiled = Appeal::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                $monthAppealsAccepted = Appeal::query()
                    ->where('status', AppealStatus::Accepted)
                    ->whereBetween('updated_at', [$start, $end])
                    ->count();

                $monthlyTrends[] = [
                    'period' => $periodKey,
                    'verified_reports' => $monthVerified,
                    'appeals_filed' => $monthAppealsFiled,
                    'appeals_accepted' => $monthAppealsAccepted,
                ];

                $currentPeriod->addMonth();
            }

            // Appeals statistics
            $totalAppeals = Appeal::query()->count();
            $acceptedAppeals = Appeal::query()->where('status', AppealStatus::Accepted)->count();
            $rejectedAppeals = Appeal::query()->where('status', AppealStatus::Rejected)->count();
            $pendingAppeals = Appeal::query()->where('status', AppealStatus::Open)->count();
            $acceptanceRate = $totalAppeals > 0 ? round(($acceptedAppeals / $totalAppeals) * 100, 1) : 0.0;

            return [
                'metadata' => [
                    'title' => 'No More Silence Aggregate Research Dataset',
                    'license' => 'Creative Commons Attribution 4.0 International (CC-BY 4.0)',
                    'legal_compliance' => 'In strict compliance with Section 14 of the Prevention of Women and Children Repression Act 2000 (Nari o Shishu Nirjatan Daman Ain), zero victim, witness, or minor identities are recorded or published. All records reflect anonymized aggregates.',
                    'generated_at' => Carbon::now()->toIso8601String(),
                ],
                'totals' => [
                    'verified_reports' => $verifiedReportsCount,
                    'incidents_covered' => $incidentsCount,
                    'districts_affected' => $districtsAffected,
                    'appeals_processed' => $totalAppeals - $pendingAppeals,
                ],
                'categories' => $categoriesBreakdown,
                'districts' => $districtsData,
                'monthly_trends' => $monthlyTrends,
                'appeals_resolution' => [
                    'total' => $totalAppeals,
                    'accepted' => $acceptedAppeals,
                    'rejected' => $rejectedAppeals,
                    'pending' => $pendingAppeals,
                    'acceptance_rate' => $acceptanceRate,
                ],
            ];
        });
    }

    /**
     * Generate CSV for districts aggregate data.
     */
    public function getDistrictsCsv(): string
    {
        $summary = $this->getSummary();
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            return '';
        }

        fputcsv($stream, ['bbs_code', 'district_en', 'district_bn', 'division_en', 'population', 'verified_reports', 'rate_per_100k']);

        foreach ($summary['districts'] as $row) {
            fputcsv($stream, [
                $row['bbs_code'],
                $row['name_en'],
                $row['name_bn'],
                $row['division_en'],
                $row['population'],
                $row['verified_reports'],
                $row['rate_per_100k'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv !== false ? $csv : '';
    }

    /**
     * Generate CSV for harassment categories aggregate data.
     */
    public function getCategoriesCsv(): string
    {
        $summary = $this->getSummary();
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            return '';
        }

        fputcsv($stream, ['category_key', 'category_en', 'category_bn', 'verified_reports', 'percentage']);

        foreach ($summary['categories'] as $row) {
            fputcsv($stream, [
                $row['key'],
                $row['label_en'],
                $row['label_bn'],
                $row['count'],
                $row['percentage'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv !== false ? $csv : '';
    }

    /**
     * Generate CSV for monthly trends aggregate data.
     */
    public function getMonthlyCsv(): string
    {
        $summary = $this->getSummary();
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            return '';
        }

        fputcsv($stream, ['period', 'verified_reports', 'appeals_filed', 'appeals_accepted']);

        foreach ($summary['monthly_trends'] as $row) {
            fputcsv($stream, [
                $row['period'],
                $row['verified_reports'],
                $row['appeals_filed'],
                $row['appeals_accepted'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv !== false ? $csv : '';
    }
}
