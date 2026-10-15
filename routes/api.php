<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PublicDistrictController;
use App\Http\Controllers\Api\V1\PublicIncidentController;
use App\Http\Controllers\Api\V1\PublicProfileController;
use App\Http\Controllers\Api\V1\PublicReportController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:60,1'])->group(function (): void {
    // Stats
    Route::get('/stats', [StatsController::class, 'national'])->name('api.v1.stats.national');
    Route::get('/stats/districts', [StatsController::class, 'districts'])->name('api.v1.stats.districts');

    // Reports
    Route::get('/reports/featured', [PublicReportController::class, 'featured'])->name('api.v1.reports.featured');
    Route::get('/reports/{uuid}', [PublicReportController::class, 'show'])->name('api.v1.reports.show');

    // Search (stricter throttle for search: 30/min)
    Route::get('/search', [SearchController::class, 'search'])
        ->middleware('throttle:30,1')
        ->name('api.v1.search');

    // Profiles
    Route::get('/profiles/{id}', [PublicProfileController::class, 'show'])->name('api.v1.profiles.show');

    // Incidents
    Route::get('/incidents', [PublicIncidentController::class, 'index'])->name('api.v1.incidents.index');
    Route::get('/incidents/{slug}', [PublicIncidentController::class, 'show'])->name('api.v1.incidents.show');

    // Districts
    Route::get('/districts', [PublicDistrictController::class, 'index'])->name('api.v1.districts.index');
    Route::get('/districts/{slug}', [PublicDistrictController::class, 'show'])->name('api.v1.districts.show');
});
