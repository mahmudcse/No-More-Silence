<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProfileSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['facebook_profile_id', 'display_name', 'avatar_media_id', 'captured_at', 'captured_by'])]
class ProfileSnapshot extends Model
{
    /** @use HasFactory<ProfileSnapshotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<FacebookProfile, $this> */
    public function facebookProfile(): BelongsTo
    {
        return $this->belongsTo(FacebookProfile::class);
    }

    /** @return BelongsTo<User, $this> */
    public function capturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captured_by');
    }
}
