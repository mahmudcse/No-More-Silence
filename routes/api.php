<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AppealController;
use App\Http\Controllers\Api\V1\PublicDistrictController;
use App\Http\Controllers\Api\V1\PublicIncidentController;
use App\Http\Controllers\Api\V1\PublicProfileController;
use App\Http\Controllers\Api\V1\PublicReportController;
use App\Http\Controllers\Api\V1\ReporterAuthController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\SubmissionController;
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

    // Profiles & Evidence Package
    Route::get('/profiles/{id}', [PublicProfileController::class, 'show'])->name('api.v1.profiles.show');
    Route::get('/profiles/{id}/evidence-package', [PublicProfileController::class, 'evidencePackage'])->name('api.v1.profiles.evidence-package');

    // Incidents
    Route::get('/incidents', [PublicIncidentController::class, 'index'])->name('api.v1.incidents.index');
    Route::get('/incidents/{slug}', [PublicIncidentController::class, 'show'])->name('api.v1.incidents.show');

    // Districts
    Route::get('/districts', [PublicDistrictController::class, 'index'])->name('api.v1.districts.index');
    Route::get('/districts/{slug}', [PublicDistrictController::class, 'show'])->name('api.v1.districts.show');

    // Submissions & Tracking
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('api.v1.submissions.store');
    Route::get('/submissions/track/{code}', [SubmissionController::class, 'track'])->name('api.v1.submissions.track');

    // Appeals (Right of reply)
    Route::post('/appeals', [AppealController::class, 'store'])->name('api.v1.appeals.store');

    // Reporter Authentication & Portal
    Route::post('/auth/magic-link', [ReporterAuthController::class, 'magicLink'])->name('api.v1.auth.magic-link');
    Route::get('/auth/verify/{id}', [ReporterAuthController::class, 'verify'])->name('api.v1.auth.verify');
    Route::get('/me/submissions', [ReporterAuthController::class, 'submissions'])->name('api.v1.me.submissions');
});
