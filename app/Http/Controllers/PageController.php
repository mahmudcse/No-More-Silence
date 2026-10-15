<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Pages/About');
    }

    public function methodology(): Response
    {
        return Inertia::render('Pages/Methodology');
    }

    public function safetyResources(): Response
    {
        return Inertia::render('Pages/SafetyResources');
    }

    public function terms(): Response
    {
        return Inertia::render('Pages/Terms');
    }

    public function privacy(): Response
    {
        return Inertia::render('Pages/Privacy');
    }
}
