<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasPublicUuid;
use App\Enums\DistrictSource;
use App\Enums\Platform;
use App\Enums\ProfileStatus;
use Database\Factories\FacebookProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property DistrictSource $district_source
 * @property ProfileStatus $profile_status
 * @property Platform $platform
 */
#[Fillable(['fb_numeric_id', 'canonical_url', 'current_display_name', 'display_name_normalized', 'district_id', 'district_source', 'profile_status', 'platform', 'last_checked_at', 'public_reports_count', 'first_recorded_at', 'last_recorded_at'])]
class FacebookProfile extends Model
{
    /** @use HasFactory<FacebookProfileFactory> */
    use HasFactory;

    use HasPublicUuid;

    protected function casts(): array
    {
        return [
            'district_source' => DistrictSource::class,
            'profile_status' => ProfileStatus::class,
            'platform' => Platform::class,
            'last_checked_at' => 'datetime',
            'first_recorded_at' => 'datetime',
            'last_recorded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<District, $this> */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /** @return HasMany<ProfileAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(ProfileAlias::class);
    }

    /** @return HasMany<ProfileSnapshot, $this> */
    public function snapshots(): HasMany
    {
        return $this->hasMany(ProfileSnapshot::class);
    }

    /** @return HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** @return HasMany<Appeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(Appeal::class);
    }
}
