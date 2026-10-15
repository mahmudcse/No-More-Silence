<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\SearchController as ApiSearchController;
use App\Http\Resources\PublicProfileResource;
use App\Models\FacebookProfile;
use App\Models\ProfileAlias;
use App\Support\FacebookUrlNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class WebSearchController extends Controller
{
    public function index(Request $request): Response
    {
        $rawQuery = (string) $request->query('q', '');
        $query = trim($rawQuery);

        $type = 'empty';
        $warning = null;
        $warningEn = null;
        $results = [];

        if ($query !== '') {
            if (str_starts_with($query, 'http') || str_contains($query, 'facebook.com') || str_contains($query, 'fb.com')) {
                $type = 'url';
                $normalizedUrl = FacebookUrlNormalizer::normalize($query);

                $profile = FacebookProfile::query()
                    ->where('canonical_url', $normalizedUrl)
                    ->where('public_reports_count', '>', 0)
                    ->with(['district', 'reports'])
                    ->first();

                if (! $profile) {
                    $alias = ProfileAlias::query()
                        ->where('alias_url', $normalizedUrl)
                        ->first();

                    if ($alias && $alias->facebookProfile && $alias->facebookProfile->public_reports_count > 0) {
                        $profile = $alias->facebookProfile;
                        $profile->load(['district', 'reports']);
                    }
                }

                $results = $profile ? [new PublicProfileResource($profile)] : [];
            } else {
                $type = 'name';
                $warning = ApiSearchController::SAME_NAME_WARNING_BN;
                $warningEn = ApiSearchController::SAME_NAME_WARNING_EN;
                $cleanName = mb_strtolower($query);

                if (DB::getDriverName() === 'pgsql') {
                    $profiles = FacebookProfile::query()
                        ->where('public_reports_count', '>', 0)
                        ->where(function ($q) use ($cleanName) {
                            $q->whereRaw('similarity(display_name_normalized, ?) > 0.25', [$cleanName])
                                ->orWhere('display_name_normalized', 'ILIKE', "%{$cleanName}%");
                        })
                        ->orderByRaw('similarity(display_name_normalized, ?) DESC', [$cleanName])
                        ->with('district')
                        ->limit(20)
                        ->get();
                } else {
                    $profiles = FacebookProfile::query()
                        ->where('public_reports_count', '>', 0)
                        ->where('display_name_normalized', 'LIKE', "%{$cleanName}%")
                        ->with('district')
                        ->limit(20)
                        ->get();
                }

                $results = PublicProfileResource::collection($profiles)->resolve();
            }
        }

        return Inertia::render('Search', [
            'initialQuery' => $query,
            'searchType' => $type,
            'warning' => $warning,
            'warningEn' => $warningEn,
            'results' => $results,
        ]);
    }
}
