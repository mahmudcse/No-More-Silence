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
     * Get featured verified reports for the homepage feed.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 12);
        if ($limit < 1 || $limit > 50) {
            $limit = 12;
        }

        $reports = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->with(['facebookProfile', 'incident.district', 'media'])
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
