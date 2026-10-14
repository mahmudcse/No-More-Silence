<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AliasType;
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

    /** @return BelongsTo<FacebookProfile, $this> */
    public function facebookProfile(): BelongsTo
    {
        return $this->belongsTo(FacebookProfile::class);
    }
}
