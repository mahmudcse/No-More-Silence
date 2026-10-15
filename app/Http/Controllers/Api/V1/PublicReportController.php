<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    /**
     * Get recent reports (verified and pending) with pagination for infinite scroll.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 8);
        if ($limit < 1 || $limit > 30) {
            $limit = 8;
        }

        $reports = Report::query()
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
            ->latest('created_at')
            ->paginate($limit);

        return response()->json([
            'data' => PublicReportResource::collection($reports->items()),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'has_more' => $reports->hasMorePages(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * Get featured verified reports for the homepage feed.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 12);
        if ($limit < 1 || $limit > 50) {
            $limit = 12;
        }

        $reports = Report::query()
            ->where('is_hidden_from_homepage', false)
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->with(['facebookProfile', 'incident.district', 'media'])
            ->withCount(['comments' => fn ($q) => $q->where('is_hidden', false)])
            ->latest('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => PublicReportResource::collection($reports),
        ]);
    }

    /**
     * Get a single public report by UUID.
     */
    public function show(string $uuid): JsonResponse
    {
        $report = Report::query()
            ->where('uuid', $uuid)
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->with(['facebookProfile', 'incident.district', 'media'])
            ->firstOrFail();

        return response()->json([
            'data' => new PublicReportResource($report),
        ]);
    }
}
