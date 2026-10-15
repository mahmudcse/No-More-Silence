<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Soft, expiring lock that prevents two moderators from reviewing the same report at once.
 */
final class ReportLock
{
    public const TTL_SECONDS = 900;

    /**
     * Acquires (or refreshes) the lock for the given admin. Returns false if another admin holds it.
     */
    public function acquire(Report $report, User $admin): bool
    {
        $key = $this->key($report);

        if (Cache::add($key, $admin->id, self::TTL_SECONDS)) {
            return true;
        }

        if ($this->holderId($report) !== $admin->id) {
            return false;
        }

        Cache::put($key, $admin->id, self::TTL_SECONDS);

        return true;
    }

    public function release(Report $report, User $admin): void
    {
        if ($this->holderId($report) === $admin->id) {
            Cache::forget($this->key($report));
        }
    }

    public function holderId(Report $report): ?int
    {
        $holder = Cache::get($this->key($report));

        return $holder !== null ? (int) $holder : null;
    }

    public function isLockedForOthers(Report $report, User $admin): bool
    {
        $holder = $this->holderId($report);

        return $holder !== null && $holder !== $admin->id;
    }

    private function key(Report $report): string
    {
        return "report-review-lock:{$report->getKey()}";
    }
}
