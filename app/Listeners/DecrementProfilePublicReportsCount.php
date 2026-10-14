<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReportRetracted;
use App\Models\FacebookProfile;

class DecrementProfilePublicReportsCount
{
    public function handle(ReportRetracted $event): void
    {
        if (! $event->wasPublic || $event->report->facebook_profile_id === null) {
            return;
        }

        FacebookProfile::whereKey($event->report->facebook_profile_id)
            ->where('public_reports_count', '>', 0)
            ->decrement('public_reports_count');
    }
}
