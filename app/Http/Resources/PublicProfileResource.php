<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use App\Support\BanglaNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FacebookProfile
 */
class PublicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     uuid: string,
     *     display_name: string|null,
     *     canonical_url: string,
     *     district: array{name_en: string, name_bn: string, slug: string}|null,
     *     district_source: string,
     *     public_reports_count: int,
     *     public_reports_count_bn: string,
     *     first_recorded_at: string|null,
     *     last_recorded_at: string|null,
     *     reports?: AnonymousResourceCollection
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'display_name' => $this->current_display_name,
            'canonical_url' => $this->canonical_url,
            'district' => $this->district ? [
                'name_en' => $this->district->name_en,
                'name_bn' => $this->district->name_bn,
                'slug' => $this->district->slug,
            ] : null,
            'district_source' => $this->district_source->value,
            'public_reports_count' => $this->public_reports_count,
            'public_reports_count_bn' => BanglaNumber::convert($this->public_reports_count),
            'first_recorded_at' => $this->first_recorded_at?->toIso8601String(),
            'last_recorded_at' => $this->last_recorded_at?->toIso8601String(),
            'reports' => $this->whenLoaded('reports', function () {
                return PublicReportResource::collection(
                    $this->reports->where('workflow_status', WorkflowStatus::Verified)
                        ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
                );
            }),
        ];
    }
}
