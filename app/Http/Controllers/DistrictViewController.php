<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Http\Resources\PublicDistrictResource;
use App\Http\Resources\PublicReportResource;
use App\Models\District;
use App\Models\Report;
use App\Services\StatsService;
use Inertia\Inertia;
use Inertia\Response;

class DistrictViewController extends Controller
{
    public function index(StatsService $statsService): Response
    {
        $districts = District::query()
            ->with('division')
            ->orderBy('name_en')
            ->get();

        return Inertia::render('Districts/Index', [
            'districts' => PublicDistrictResource::collection($districts)->resolve(),
            'incidentStats' => $statsService->getDistrictStats('incident'),
            'commenterStats' => $statsService->getDistrictStats('commenter'),
        ]);
    }

    public function show(string $slug): Response
    {
        $district = District::query()
            ->where('slug', $slug)
            ->with('division')
            ->firstOrFail();

        $reports = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->where(function ($q) use ($district): void {
                $q->whereHas('incident', fn ($iq) => $iq->where('district_id', $district->id))
                    ->orWhereHas('facebookProfile', fn ($pq) => $pq->where('district_id', $district->id));
            })
            ->with(['facebookProfile.district', 'media'])
            ->latest('comment_posted_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Districts/Show', [
            'district' => (new PublicDistrictResource($district))->resolve(),
            'reports' => PublicReportResource::collection($reports)->response()->getData(true),
        ]);
    }
}
