<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransparencyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TransparencyController extends Controller
{
    public function __construct(
        protected TransparencyService $transparencyService
    ) {}

    /**
     * Get historical transparency reports.
     */
    public function index(): JsonResponse
    {
        $reports = $this->transparencyService->getHistoricalReports(12);

        return response()->json([
            'reports' => $reports,
        ])
            ->setSharedMaxAge(3600)
            ->setMaxAge(300);
    }

    /**
     * Get transparency report for a specific year and month.
     */
    public function show(int $year, int $month): JsonResponse
    {
        if ($month < 1 || $month > 12 || $year < 2020 || $year > (int) Carbon::now()->addYear()->year) {
            return response()->json([
                'message' => 'Invalid year or month specified.',
            ], 422);
        }

        $report = $this->transparencyService->getMonthlyReport($year, $month);

        return response()->json($report)
            ->setSharedMaxAge(3600)
            ->setMaxAge(300);
    }
}
