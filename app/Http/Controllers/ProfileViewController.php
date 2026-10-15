<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PublicProfileResource;
use App\Models\FacebookProfile;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileViewController extends Controller
{
    public function show(string $uuid): Response
    {
        if (! Str::isUuid($uuid)) {
            abort(404);
        }

        $profile = FacebookProfile::query()
            ->where('uuid', $uuid)
            ->where('public_reports_count', '>', 0)
            ->with(['district', 'reports.media', 'reports.incident.district'])
            ->firstOrFail();

        return Inertia::render('Profiles/Show', [
            'profile' => (new PublicProfileResource($profile))->resolve(),
        ]);
    }
}
