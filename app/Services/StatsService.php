<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\FacebookProfile;
use App\Models\Report;
use App\Models\StatsSnapshot;
use App\Support\BanglaNumber;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class StatsService
{
    /**
     * Generate daily snapshots for national and per-district metrics.
     */
    public function generateSnapshots(?CarbonInterface $date = null): void
    {
        $targetDate = $date ? $date->toDateString() : Carbon::today()->toDateString();

        $nationalReceived = Report::query()->count();
        $nationalVerified = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->count();
        $nationalPublic = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->count();
        $nationalProfiles = FacebookProfile::query()
            ->where('public_reports_count', '>', 0)
            ->count();

        StatsSnapshot::query()->updateOrCreate(
            ['date' => $targetDate, 'district_id' => null],
            [
                'received' => $nationalReceived,
                'verified' => $nationalVerified,
                'public' => $nationalPublic,
                'profiles' => $nationalProfiles,
            ]
        );

        $districts = District::all();
        foreach ($districts as $district) {
            $incidentCount = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                ->whereHas('incident', function ($query) use ($district) {
                    $query->where('district_id', $district->id);
                })
                ->count();

            $profilesCount = FacebookProfile::query()
                ->where('district_id', $district->id)
                ->where('public_reports_count', '>', 0)
                ->count();

            StatsSnapshot::query()->updateOrCreate(
                ['date' => $targetDate, 'district_id' => $district->id],
                [
                    'received' => 0,
                    'verified' => $incidentCount,
                    'public' => $incidentCount,
                    'profiles' => $profilesCount,
                ]
            );
        }

        $this->clearCache();
    }

    /**
     * Get aggregate national statistics (cached).
     *
     * @return array{
     *     received: int,
     *     verified: int,
     *     public: int,
     *     profiles: int,
     *     districts: int,
     *     received_bn: string,
     *     verified_bn: string,
     *     public_bn: string,
     *     profiles_bn: string,
     *     districts_bn: string
     * }
     */
    public function getNationalStats(): array
    {
        /** @var array{
         *     received: int,
         *     verified: int,
         *     public: int,
         *     profiles: int,
         *     districts: int,
         *     received_bn: string,
         *     verified_bn: string,
         *     public_bn: string,
         *     profiles_bn: string,
         *     districts_bn: string
         * } $stats */
        $stats = Cache::remember('stats.national', 3600, function () {
            $received = Report::query()->count();
            $verified = Report::query()->where('workflow_status', WorkflowStatus::Verified)->count();
            $public = Report::query()
                ->where('workflow_status', WorkflowStatus::Verified)
                ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                ->count();
            $profiles = FacebookProfile::query()->where('public_reports_count', '>', 0)->count();

            $districtsCount = District::query()
                ->whereHas('incidents.reports', function ($query) {
                    $query->where('workflow_status', WorkflowStatus::Verified)
                        ->whereIn('visibility', [Visibility::Public, Visibility::Featured]);
                })
                ->count();

            return [
                'received' => $received,
                'verified' => $verified,
                'public' => $public,
                'profiles' => $profiles,
                'districts' => $districtsCount,
                'received_bn' => BanglaNumber::convert($received),
                'verified_bn' => BanglaNumber::convert($verified),
                'public_bn' => BanglaNumber::convert($public),
                'profiles_bn' => BanglaNumber::convert($profiles),
                'districts_bn' => BanglaNumber::convert($districtsCount),
            ];
        });

        return $stats;
    }

    /**
     * Get district map statistics (cached).
     *
     * @return array<int, array{
     *     id: int,
     *     code: string,
     *     slug: string,
     *     name_en: string,
     *     name_bn: string,
     *     division: string,
     *     population: int,
     *     count: int,
     *     count_bn: string,
     *     rate_per_100k: float,
     *     rate_per_100k_bn: string
     * }>
     */
    public function getDistrictStats(string $mode = 'incident'): array
    {
        $cacheKey = "stats.districts.{$mode}";

        /** @var array<int, array{
         *     id: int,
         *     code: string,
         *     slug: string,
         *     name_en: string,
         *     name_bn: string,
         *     division: string,
         *     population: int,
         *     count: int,
         *     count_bn: string,
         *     rate_per_100k: float,
         *     rate_per_100k_bn: string
         * }> $stats */
        $stats = Cache::remember($cacheKey, 3600, function () use ($mode) {
            $districts = District::with('division')->get();

            $result = [];
            foreach ($districts as $district) {
                if ($mode === 'commenter') {
                    $count = FacebookProfile::query()
                        ->where('district_id', $district->id)
                        ->where('public_reports_count', '>', 0)
                        ->count();
                } else {
                    $count = Report::query()
                        ->where('workflow_status', WorkflowStatus::Verified)
                        ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                        ->whereHas('incident', function ($query) use ($district) {
                            $query->where('district_id', $district->id);
                        })
                        ->count();
                }

                $population = (int) ($district->population ?? 0);
                $rate = $population > 0 ? round(($count / $population) * 100000, 2) : 0.0;

                $result[] = [
                    'id' => $district->id,
                    'code' => $district->code,
                    'slug' => $district->slug,
                    'name_en' => $district->name_en,
                    'name_bn' => $district->name_bn,
                    'division' => $district->division->name_en ?? '',
                    'population' => $population,
                    'count' => $count,
                    'count_bn' => BanglaNumber::convert($count),
                    'rate_per_100k' => $rate,
                    'rate_per_100k_bn' => BanglaNumber::convert($rate),
                ];
            }

            return $result;
        });

        return $stats;
    }

    public function clearCache(): void
    {
        Cache::forget('stats.national');
        Cache::forget('stats.districts.incident');
        Cache::forget('stats.districts.commenter');
    }
}
