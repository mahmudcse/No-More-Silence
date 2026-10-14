<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\Platform;
use App\Enums\ReportCategory;
use App\Enums\SubmittedVia;
use DateTimeInterface;

final readonly class NewReportData
{
    public function __construct(
        public ?string $submittedProfileUrl = null,
        public ?string $submittedCommentUrl = null,
        public ?string $commentText = null,
        public ?string $commentTextTranslation = null,
        public ?DateTimeInterface $commentPostedAt = null,
        public ?ReportCategory $category = null,
        public ?int $severity = null,
        public ?int $reporterId = null,
        public ?int $facebookProfileId = null,
        public ?int $incidentId = null,
        public Platform $platform = Platform::Facebook,
        public SubmittedVia $submittedVia = SubmittedVia::Web,
        public ?string $submitterIpHash = null,
    ) {}
}
