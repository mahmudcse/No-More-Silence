<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProfileResource;
use App\Models\FacebookProfile;
use App\Models\ProfileAlias;
use App\Support\FacebookUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public const SAME_NAME_WARNING_BN = 'সতর্কতা: বাংলাদেশে একই নামে বহু মানুষ থাকতে পারেন। প্রোফাইলের সুনির্দিষ্ট লিঙ্ক ও আইডি নিশ্চিত না হয়ে কাউকে দোষারোপ করবেন না।';

    public const SAME_NAME_WARNING_EN = 'Warning: Multiple individuals may share the same name in Bangladesh. Do not attribute comments to any person without confirming their specific profile link and numeric ID.';

    public function search(Request $request): JsonResponse
    {
        $rawQuery = (string) $request->query('q', '');
        $query = trim($rawQuery);

        if ($query === '') {
            return response()->json([
                'query' => '',
                'type' => 'empty',
                'warning' => null,
                'warning_en' => null,
                'data' => [],
            ]);
        }

        // Check if query is a URL
        if (str_starts_with($query, 'http') || str_contains($query, 'facebook.com') || str_contains($query, 'fb.com')) {
            $normalizedUrl = FacebookUrlNormalizer::normalize($query);

            $profile = FacebookProfile::query()
                ->where('canonical_url', $normalizedUrl)
                ->where('public_reports_count', '>', 0)
                ->with(['district', 'reports'])
                ->first();

            if (! $profile) {
                // Check aliases
                $alias = ProfileAlias::query()
                    ->where('alias_url', $normalizedUrl)
                    ->first();

                if ($alias && $alias->facebookProfile && $alias->facebookProfile->public_reports_count > 0) {
                    $profile = $alias->facebookProfile;
                    $profile->load(['district', 'reports']);
                }
            }

            return response()->json([
                'query' => $query,
                'type' => 'url',
                'normalized_url' => $normalizedUrl,
                'warning' => null,
                'warning_en' => null,
                'data' => $profile ? [new PublicProfileResource($profile)] : [],
            ]);
        }

        // Search by name (trigram for pgsql, LIKE for sqlite / fallback)
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

        return response()->json([
            'query' => $query,
            'type' => 'name',
            'warning' => self::SAME_NAME_WARNING_BN,
            'warning_en' => self::SAME_NAME_WARNING_EN,
            'data' => PublicProfileResource::collection($profiles),
        ]);
    }
}
