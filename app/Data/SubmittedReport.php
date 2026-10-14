<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Report;

final readonly class SubmittedReport
{
    public function __construct(
        public Report $report,
        public string $trackingCode,
    ) {}
}
