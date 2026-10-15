<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppealViewController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Appeals/Create', [
            'prefilledProfileUrl' => $request->query('profile_url', ''),
            'prefilledReportUuid' => $request->query('report_uuid', ''),
            'turnstileSiteKey' => config('services.turnstile.site_key'),
        ]);
    }
}
