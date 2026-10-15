<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\OpenDataService;
use Inertia\Inertia;
use Inertia\Response;

class OpenDataViewController extends Controller
{
    public function __construct(
        protected OpenDataService $openDataService
    ) {}

    public function index(): Response
    {
        $summary = $this->openDataService->getSummary();

        return Inertia::render('OpenData/Index', [
            'summary' => $summary,
        ]);
    }
}
