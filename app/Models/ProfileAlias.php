<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AliasType;
use App\Support\FacebookUrlNormalizer;
use App\Support\NameNormalizer;
use Database\Factories\ProfileAliasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AliasType $type
 */
#[Fillable(['facebook_profile_id', 'type', 'value', 'value_normalized', 'seen_at'])]
class ProfileAlias extends Model
{
    /** @use HasFactory<ProfileAliasFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AliasType::class,
            'seen_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProfileAlias $alias): void {
            $alias->value_normalized = match ($alias->type) {
                AliasType::Url => app(FacebookUrlNormalizer::class)->normalize($alias->value) ?? $alias->value,
                AliasType::Name => app(NameNormalizer::class)->normalize($alias->value),
            };
        });
    }

    /** @return BelongsTo<FacebookProfile, $this> */
    public function facebookProfile(): BelongsTo
    {
        return $this->belongsTo(FacebookProfile::class);
    }
}
