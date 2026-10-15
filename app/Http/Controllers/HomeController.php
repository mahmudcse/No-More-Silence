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
        $reportsQuery = Report::query()
            ->where('is_hidden_from_homepage', false)
            ->where(function ($q): void {
                $q->where(function ($vq): void {
                    $vq->where('workflow_status', WorkflowStatus::Verified)
                        ->whereIn('visibility', [Visibility::Public, Visibility::Featured]);
                })
                    ->orWhere(function ($uq): void {
                        $uq->whereIn('workflow_status', [
                            WorkflowStatus::Submitted,
                            WorkflowStatus::InVerification,
                            WorkflowStatus::Triaged,
                        ]);
                    });
            })
            ->with(['facebookProfile', 'incident.district', 'media'])
            ->withCount(['comments' => fn ($q) => $q->where('is_hidden', false)])
            ->latest('created_at');

        $paginated = $reportsQuery->paginate(8);

        return Inertia::render('Welcome', [
            'nationalStats' => $statsService->getNationalStats(),
            'districtStats' => $statsService->getDistrictStats('commenter'),
            'featuredReports' => PublicReportResource::collection($paginated->items())->resolve(),
            'initialPagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'has_more' => $paginated->hasMorePages(),
                'total' => $paginated->total(),
            ],
        ]);
    }
}
