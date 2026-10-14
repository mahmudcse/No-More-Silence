<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppealStatus;
use Database\Factories\AppealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AppealStatus $status
 */
#[Fillable(['facebook_profile_id', 'report_id', 'contact', 'ownership_proof', 'message', 'status', 'outcome_note', 'right_of_reply_text', 'handled_by'])]
class Appeal extends Model
{
    /** @use HasFactory<AppealFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'contact' => 'encrypted',
            'status' => AppealStatus::class,
        ];
    }

    /** @return BelongsTo<FacebookProfile, $this> */
    public function facebookProfile(): BelongsTo
    {
        return $this->belongsTo(FacebookProfile::class);
    }

    /** @return BelongsTo<Report, $this> */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /** @return BelongsTo<User, $this> */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
