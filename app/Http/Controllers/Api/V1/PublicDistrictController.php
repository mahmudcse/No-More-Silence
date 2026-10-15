<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicDistrictResource;
use App\Models\District;
use Illuminate\Http\JsonResponse;

class PublicDistrictController extends Controller
{
    /**
     * Get all districts list.
     */
    public function index(): JsonResponse
    {
        $districts = District::query()
            ->with('division')
            ->orderBy('name_en')
            ->get();

        return response()->json([
            'data' => PublicDistrictResource::collection($districts),
        ]);
    }

    /**
     * Get a single district by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $district = District::query()
            ->where('slug', $slug)
            ->with(['division', 'incidents' => function ($q) {
                $q->withCount(['reports' => function ($rq) {
                    $rq->where('workflow_status', 'verified')->whereIn('visibility', ['public', 'featured']);
                }]);
            }])
            ->firstOrFail();

        return response()->json([
            'data' => new PublicDistrictResource($district),
        ]);
    }
}
