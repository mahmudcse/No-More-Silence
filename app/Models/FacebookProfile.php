<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasPublicUuid;
use App\Enums\AliasType;
use App\Enums\DistrictSource;
use App\Enums\Platform;
use App\Enums\ProfileStatus;
use App\Support\FacebookUrlNormalizer;
use App\Support\NameNormalizer;
use Database\Factories\FacebookProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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

    protected static function booted(): void
    {
        static::saving(function (FacebookProfile $profile): void {
            $profile->display_name_normalized = app(NameNormalizer::class)->normalize($profile->current_display_name);

            $canonical = app(FacebookUrlNormalizer::class)->normalize((string) $profile->canonical_url);

            if ($canonical !== null) {
                $profile->canonical_url = $canonical;

                if ($profile->fb_numeric_id === null && preg_match('/profile\.php\?id=(\d+)$/', $canonical, $matches) === 1) {
                    $profile->fb_numeric_id = $matches[1];
                }
            }
        });
    }

    /**
     * Matches profiles whose canonical URL, or any previously seen URL alias, equals the normalised URL.
     *
     * @param  Builder<FacebookProfile>  $query
     */
    #[Scope]
    protected function matchingUrl(Builder $query, string $url): void
    {
        $canonical = app(FacebookUrlNormalizer::class)->normalize($url);

        if ($canonical === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(fn (Builder $query) => $query
            ->where('canonical_url', $canonical)
            ->orWhereHas('aliases', fn (Builder $alias) => $alias
                ->where('type', AliasType::Url)
                ->where('value_normalized', $canonical)));
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
