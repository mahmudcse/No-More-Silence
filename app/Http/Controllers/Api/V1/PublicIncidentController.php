<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicIncidentResource;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicIncidentController extends Controller
{
    /**
     * Get paginated incidents list.
     */
    public function index(Request $request): JsonResponse
    {
        $incidents = Incident::query()
            ->with(['district'])
            ->withCount(['reports' => function ($query) {
                $query->where('workflow_status', 'verified')->whereIn('visibility', ['public', 'featured']);
            }])
            ->latest('occurred_on')
            ->paginate(15);

        return response()->json($incidents);
    }

    /**
     * Get a single incident by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $incident = Incident::query()
            ->where('slug', $slug)
            ->with(['district', 'reports.facebookProfile', 'reports.media'])
            ->firstOrFail();

        return response()->json([
            'data' => new PublicIncidentResource($incident),
        ]);
    }
}
