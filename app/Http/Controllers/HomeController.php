<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Http\Resources\PublicReportResource;
use App\Models\Report;
use App\Services\StatsService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(StatsService $statsService): Response
    {
        $featuredReports = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->with(['facebookProfile', 'incident.district', 'media'])
            ->latest('published_at')
            ->limit(6)
            ->get();

        return Inertia::render('Welcome', [
            'nationalStats' => $statsService->getNationalStats(),
            'districtStats' => $statsService->getDistrictStats('incident'),
            'featuredReports' => PublicReportResource::collection($featuredReports)->resolve(),
        ]);
    }
}
