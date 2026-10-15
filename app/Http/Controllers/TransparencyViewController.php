<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TransparencyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransparencyViewController extends Controller
{
    public function __construct(
        protected TransparencyService $transparencyService
    ) {}

    public function index(Request $request): Response
    {
        $year = $request->filled('year') ? (int) $request->query('year') : (int) Carbon::now()->year;
        $month = $request->filled('month') ? (int) $request->query('month') : (int) Carbon::now()->month;

        $currentReport = $this->transparencyService->getMonthlyReport($year, $month);
        $history = $this->transparencyService->getHistoricalReports(6);

        return Inertia::render('Transparency/Index', [
            'currentReport' => $currentReport,
            'history' => $history,
            'selectedYear' => $year,
            'selectedMonth' => $month,
        ]);
    }
}
