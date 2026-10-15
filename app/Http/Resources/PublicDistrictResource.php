<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\District;
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
     *     incidents_count?: int,
     *     incidents_count_bn?: string
     * }
     */
    public function toArray(Request $request): array
    {
        $population = (int) ($this->population ?? 0);

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
            'incidents_count' => $this->incidents_count ?? $this->incidents()->count(),
            'incidents_count_bn' => BanglaNumber::convert($this->incidents_count ?? $this->incidents()->count()),
        ];
    }
}
