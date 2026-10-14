<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReporterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['email', 'display_handle', 'reputation', 'is_banned'])]
class Reporter extends Model
{
    /** @use HasFactory<ReporterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'reputation' => 'integer',
            'is_banned' => 'boolean',
        ];
    }

    /** @return HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
