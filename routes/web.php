<?php

declare(strict_types=1);

use App\Http\Controllers\AppealViewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DistrictViewController;
use App\Http\Controllers\FeedbackViewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncidentViewController;
use App\Http\Controllers\OpenDataViewController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileViewController;
use App\Http\Controllers\ReporterViewController;
use App\Http\Controllers\SubmissionViewController;
use App\Http\Controllers\TransparencyViewController;
use App\Http\Controllers\WebSearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Website Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [WebSearchController::class, 'index'])->name('search');

// Profiles, Incidents, Districts
Route::get('/profiles/{uuid}', [ProfileViewController::class, 'show'])->whereUuid('uuid')->name('profiles.show');
Route::get('/incidents', [IncidentViewController::class, 'index'])->name('incidents.index');
Route::get('/incidents/{slug}', [IncidentViewController::class, 'show'])->name('incidents.show');
Route::get('/districts', [DistrictViewController::class, 'index'])->name('districts.index');
Route::get('/districts/{slug}', [DistrictViewController::class, 'show'])->name('districts.show');

// Informational & Policy Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/methodology', [PageController::class, 'methodology'])->name('methodology');
Route::get('/safety-resources', [PageController::class, 'safetyResources'])->name('safety-resources');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/open-data', [OpenDataViewController::class, 'index'])->name('open-data');
Route::get('/transparency', [TransparencyViewController::class, 'index'])->name('transparency');
Route::get('/feedback', [FeedbackViewController::class, 'index'])->name('feedback');

// Submissions, Tracking, Appeals & Reporter Portal
Route::get('/submit', [SubmissionViewController::class, 'create'])->name('submit');
Route::post('/submit/share-target', [SubmissionViewController::class, 'shareTarget'])->name('submit.share-target');
Route::get('/submit/shared-file/{shareId}/{filename}', [SubmissionViewController::class, 'sharedFile'])->name('submissions.shared-file');
Route::get('/track/{code?}', [SubmissionViewController::class, 'track'])->name('track');
Route::get('/appeals', [AppealViewController::class, 'create'])->name('appeals.create');
Route::get('/my-submissions', [ReporterViewController::class, 'dashboard'])->name('reporter.dashboard');

// Report Discussion & Comments
Route::get('/reports/{reportUuid}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::middleware('auth')->group(function () {
    Route::post('/reports/{reportUuid}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike'])->name('comments.like');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// PWA & TWA Static Endpoints
Route::get('/manifest.webmanifest', fn () => response()->file(public_path('manifest.webmanifest'), [
    'Content-Type' => 'application/manifest+json',
]))->name('pwa.manifest');

Route::get('/manifest.json', fn () => response()->file(public_path('manifest.json'), [
    'Content-Type' => 'application/manifest+json',
]));

Route::get('/sw.js', fn () => response()->file(public_path('sw.js'), [
    'Content-Type' => 'application/javascript',
]))->name('pwa.sw');

Route::get('/offline.html', fn () => response()->file(public_path('offline.html')))->name('pwa.offline');

Route::get('/.well-known/assetlinks.json', fn () => response()->file(public_path('.well-known/assetlinks.json'), [
    'Content-Type' => 'application/json',
]))->name('twa.assetlinks');

// User Auth & Dashboard (Internal / Staff)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Filament Admin Panel Locale Switcher
Route::get('/admin/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'bn'], true)) {
        session(['filament_locale' => $locale]);
    }

    return redirect()->back(fallback: url('/admin'));
})->name('filament.admin.locale.switch')->middleware(['web']);

require __DIR__.'/auth.php';
