<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PublicIncidentResource;
use App\Models\Incident;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentViewController extends Controller
{
    public function index(Request $request): Response
    {
        $incidents = Incident::query()
            ->with(['district'])
            ->withCount(['reports' => function ($query) {
                $query->where('workflow_status', 'verified')->whereIn('visibility', ['public', 'featured']);
            }])
            ->latest('occurred_on')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Incidents/Index', [
            'incidents' => $incidents,
        ]);
    }

    public function show(string $slug): Response
    {
        $incident = Incident::query()
            ->where('slug', $slug)
            ->with(['district', 'reports.facebookProfile', 'reports.media'])
            ->firstOrFail();

        return Inertia::render('Incidents/Show', [
            'incident' => (new PublicIncidentResource($incident))->resolve(),
        ]);
    }
}
