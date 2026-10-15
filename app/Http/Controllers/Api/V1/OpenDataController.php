<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\OpenDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OpenDataController extends Controller
{
    public function __construct(
        protected OpenDataService $openDataService
    ) {}

    /**
     * Get aggregate research data summary (JSON).
     */
    public function summary(): JsonResponse
    {
        $summary = $this->openDataService->getSummary();

        return response()->json($summary)
            ->setSharedMaxAge(3600)
            ->setMaxAge(300);
    }

    /**
     * Download districts aggregate data as CSV.
     */
    public function districtsCsv(): Response
    {
        $csv = $this->openDataService->getDistrictsCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="nomoresilence_districts_data.csv"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Download harassment categories aggregate data as CSV.
     */
    public function categoriesCsv(): Response
    {
        $csv = $this->openDataService->getCategoriesCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="nomoresilence_categories_data.csv"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Download monthly trends aggregate data as CSV.
     */
    public function monthlyCsv(): Response
    {
        $csv = $this->openDataService->getMonthlyCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="nomoresilence_monthly_trends_data.csv"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
