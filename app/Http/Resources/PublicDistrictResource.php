<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Models\District;
use App\Models\Report;
use App\Support\BanglaNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin District
 */
class PublicDistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     code: string,
     *     name_en: string,
     *     name_bn: string,
     *     slug: string,
     *     population: int,
     *     population_bn: string,
     *     division: array{name_en: string, name_bn: string}|null,
     *     reports_count?: int,
     *     reports_count_bn?: string,
     *     incidents_count?: int,
     *     incidents_count_bn?: string
     * }
     */
    public function toArray(Request $request): array
    {
        $population = (int) ($this->population ?? 0);

        $reportsCount = Report::query()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->whereIn('visibility', [Visibility::Public, Visibility::Featured])
            ->where(function ($q): void {
                $q->whereHas('incident', fn ($iq) => $iq->where('district_id', $this->id))
                    ->orWhereHas('facebookProfile', fn ($pq) => $pq->where('district_id', $this->id));
            })
            ->count();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name_en' => $this->name_en,
            'name_bn' => $this->name_bn,
            'slug' => $this->slug,
            'population' => $population,
            'population_bn' => BanglaNumber::convert($population),
            'division' => $this->division ? [
                'name_en' => $this->division->name_en,
                'name_bn' => $this->division->name_bn,
            ] : null,
            'reports_count' => $reportsCount,
            'reports_count_bn' => BanglaNumber::convert($reportsCount),
            'incidents_count' => $this->incidents_count ?? $this->incidents()->count(),
            'incidents_count_bn' => BanglaNumber::convert($this->incidents_count ?? $this->incidents()->count()),
        ];
    }
}
