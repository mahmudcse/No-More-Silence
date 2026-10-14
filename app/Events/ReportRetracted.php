<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Report;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportRetracted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Report $report, public bool $wasPublic) {}
}
