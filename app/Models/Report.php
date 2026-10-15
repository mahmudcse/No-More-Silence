<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasPublicUuid;
use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property WorkflowStatus $workflow_status
 * @property Visibility $visibility
 * @property Platform $platform
 * @property ReportCategory|null $category
 * @property SubmittedVia $submitted_via
 */
#[Fillable(['tracking_code_hash', 'reporter_id', 'facebook_profile_id', 'incident_id', 'submitted_profile_url', 'submitted_comment_url', 'comment_text', 'comment_text_translation', 'comment_posted_at', 'category', 'severity', 'workflow_status', 'visibility', 'platform', 'submitted_via', 'submitted_by', 'duplicate_of_id', 'rejected_reason', 'verified_at', 'verified_by', 'published_at', 'published_by', 'submitter_ip_hash'])]
class Report extends Model implements HasMedia
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    use HasPublicUuid;
    use InteractsWithMedia;
    use LogsActivity;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'category' => ReportCategory::class,
            'severity' => 'integer',
            'workflow_status' => WorkflowStatus::class,
            'visibility' => Visibility::class,
            'platform' => Platform::class,
            'submitted_via' => SubmittedVia::class,
            'comment_posted_at' => 'datetime',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('original')
            ->useDisk(config('media-library.disk_original', 'local'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('redacted')
            ->useDisk(config('media-library.disk_redacted', 'public'))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('archive')
            ->useDisk(config('media-library.disk_archive', 'local'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('report');
    }

    /** @return BelongsTo<Reporter, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Reporter::class);
    }

    /** @return BelongsTo<FacebookProfile, $this> */
    public function facebookProfile(): BelongsTo
    {
        return $this->belongsTo(FacebookProfile::class);
    }

    /** @return BelongsTo<Incident, $this> */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** @return BelongsTo<User, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<Report, $this> */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    /** @return HasMany<ReportReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ReportReview::class);
    }

    /** @return HasMany<Appeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class);
    }
}
