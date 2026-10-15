<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\Incident;
use App\Support\BanglaNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Incident
 */
class PublicIncidentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     title_en: string,
     *     title_bn: string,
     *     slug: string,
     *     occurred_on: string|null,
     *     summary: string|null,
     *     district: array{name_en: string, name_bn: string, slug: string}|null,
     *     public_reports_count: int,
     *     public_reports_count_bn: string,
     *     reports?: AnonymousResourceCollection
     * }
     */
    public function toArray(Request $request): array
    {
        $publicReportsCount = $this->reports()
            ->where('workflow_status', 'verified')
            ->whereIn('visibility', ['public', 'featured'])
            ->count();

        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'title_bn' => $this->title_bn,
            'slug' => $this->slug,
            'occurred_on' => $this->occurred_on?->toDateString(),
            'summary' => $this->summary,
            'district' => $this->district ? [
                'name_en' => $this->district->name_en,
                'name_bn' => $this->district->name_bn,
                'slug' => $this->district->slug,
            ] : null,
            'public_reports_count' => $publicReportsCount,
            'public_reports_count_bn' => BanglaNumber::convert($publicReportsCount),
            'reports' => $this->whenLoaded('reports', function () {
                return PublicReportResource::collection(
                    $this->reports->where('workflow_status', WorkflowStatus::Verified)
                        ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                );
            }),
        ];
    }
}
