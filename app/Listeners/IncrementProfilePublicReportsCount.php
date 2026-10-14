<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReportPublished;
use App\Models\FacebookProfile;

class IncrementProfilePublicReportsCount
{
    public function handle(ReportPublished $event): void
    {
        if ($event->report->facebook_profile_id === null) {
            return;
        }

        FacebookProfile::whereKey($event->report->facebook_profile_id)
            ->increment('public_reports_count');
    }
}
