<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PublicDistrictResource;
use App\Models\District;
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
            ->with(['division', 'incidents' => function ($q) {
                $q->withCount(['reports' => function ($rq) {
                    $rq->where('workflow_status', 'verified')->whereIn('visibility', ['public', 'featured']);
                }]);
            }])
            ->firstOrFail();

        return Inertia::render('Districts/Show', [
            'district' => (new PublicDistrictResource($district))->resolve(),
            'incidents' => $district->incidents,
        ]);
    }
}
