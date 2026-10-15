<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function national(StatsService $statsService): JsonResponse
    {
        return response()->json([
            'data' => $statsService->getNationalStats(),
        ])->setSharedMaxAge(300);
    }

    public function districts(Request $request, StatsService $statsService): JsonResponse
    {
        $mode = $request->query('mode', 'incident');
        if (! in_array($mode, ['incident', 'commenter'], true)) {
            $mode = 'incident';
        }

        return response()->json([
            'mode' => $mode,
            'data' => $statsService->getDistrictStats($mode),
        ])->setSharedMaxAge(300);
    }
}
