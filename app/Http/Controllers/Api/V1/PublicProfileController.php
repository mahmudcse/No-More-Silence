<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Export\ExportEvidencePackage;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProfileResource;
use App\Models\FacebookProfile;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PublicProfileController extends Controller
{
    /**
     * Get a public profile by UUID or ID.
     */
    public function show(string $idOrUuid): JsonResponse
    {
        $profile = FacebookProfile::query()
            ->where(function ($query) use ($idOrUuid) {
                $query->where('uuid', $idOrUuid);
                if (is_numeric($idOrUuid)) {
                    $query->orWhere('id', (int) $idOrUuid);
                }
            })
            ->where('public_reports_count', '>', 0)
            ->with(['district', 'reports.media', 'reports.incident.district'])
            ->firstOrFail();

        return response()->json([
            'data' => new PublicProfileResource($profile),
        ]);
    }

    public function evidencePackage(string $idOrUuid, ExportEvidencePackage $exporter): Response
    {
        $profile = FacebookProfile::query()
            ->where(function ($query) use ($idOrUuid) {
                $query->where('uuid', $idOrUuid);
                if (is_numeric($idOrUuid)) {
                    $query->orWhere('id', (int) $idOrUuid);
                }
            })
            ->where('public_reports_count', '>', 0)
            ->firstOrFail();

        return $exporter->download($profile);
    }
}
