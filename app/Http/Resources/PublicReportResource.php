<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\WorkflowStatus;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Report
 */
class PublicReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     uuid: string,
     *     comment_text: string|null,
     *     comment_text_translation: string|null,
     *     comment_posted_at: string|null,
     *     category: string|null,
     *     category_label_bn: string|null,
     *     category_label_en: string|null,
     *     verified_at: string|null,
     *     published_at: string|null,
     *     evidence_url: string|null,
     *     profile: array{uuid: string, display_name: string|null, canonical_url: string}|null,
     *     incident: array{id: int, title_en: string, title_bn: string, slug: string, district: string|null}|null
     * }
     */
    public function toArray(Request $request): array
    {
        $redactedMedia = $this->getMedia('redacted');
        $originalMedia = $this->getMedia('original');

        $mediaItems = $redactedMedia->isNotEmpty() ? $redactedMedia : $originalMedia;

        $photos = $mediaItems->map(fn ($m): array => [
            'id' => $m->id,
            'url' => $m->getUrl(),
            'name' => $m->file_name,
            'type' => (string) $m->getCustomProperty('evidence_type', 'comment'),
        ])->values()->all();

        $primaryUrl = $photos[0]['url'] ?? null;
        $isVerified = $this->workflow_status === WorkflowStatus::Verified;

        return [
            'uuid' => $this->uuid,
            'comment_text' => $this->comment_text,
            'comment_text_translation' => $this->comment_text_translation,
            'comment_posted_at' => $this->comment_posted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'category' => $this->category?->value,
            'category_label_bn' => $this->category?->labelBn(),
            'category_label_en' => $this->category?->labelEn(),
            'workflow_status' => $this->workflow_status->value,
            'workflow_status_label' => $this->workflow_status->label(),
            'workflow_status_label_bn' => match ($this->workflow_status) {
                WorkflowStatus::Verified => 'যাচাইকৃত রেকর্ড',
                WorkflowStatus::InVerification => 'যাচাইকরণাধীন',
                WorkflowStatus::Triaged, WorkflowStatus::Submitted => 'পর্যালোচনাধীন',
                default => $this->workflow_status->label(),
            },
            'is_verified' => $isVerified,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'evidence_url' => $primaryUrl,
            'photos' => $photos,
            'photos_count' => count($photos),
            'profile' => $this->facebookProfile ? [
                'uuid' => $this->facebookProfile->uuid,
                'display_name' => $this->facebookProfile->current_display_name,
                'canonical_url' => $this->facebookProfile->canonical_url,
            ] : null,
            'incident' => $this->incident ? [
                'id' => $this->incident->id,
                'title_en' => $this->incident->title_en,
                'title_bn' => $this->incident->title_bn,
                'slug' => $this->incident->slug,
                'district' => $this->incident->district ? ($this->incident->district->name_bn ?: $this->incident->district->name_en) : null,
            ] : null,
        ];
    }
}
