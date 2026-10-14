<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StatsSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'district_id', 'received', 'verified', 'public', 'profiles'])]
class StatsSnapshot extends Model
{
    /** @use HasFactory<StatsSnapshotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'received' => 'integer',
            'verified' => 'integer',
            'public' => 'integer',
            'profiles' => 'integer',
        ];
    }

    /** @return BelongsTo<District, $this> */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
