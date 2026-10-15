<?php

declare(strict_types=1);

use App\Http\Controllers\DistrictViewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncidentViewController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileViewController;
use App\Http\Controllers\WebSearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Website Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [WebSearchController::class, 'index'])->name('search');

// Profiles, Incidents, Districts
Route::get('/profiles/{uuid}', [ProfileViewController::class, 'show'])->name('profiles.show');
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

// User Auth & Dashboard (Internal / Staff)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
