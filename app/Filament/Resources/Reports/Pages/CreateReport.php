<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports\Pages;

use App\Enums\SubmittedVia;
use App\Enums\Visibility;
use App\Enums\WorkflowStatus;
use App\Filament\Resources\Reports\ReportResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['submitted_by'] = auth()->id();
        $data['submitted_via'] = SubmittedVia::Admin;
        $data['workflow_status'] = WorkflowStatus::Submitted;
        $data['visibility'] = Visibility::Hidden;

        if (empty($data['tracking_code_hash'])) {
            $data['tracking_code_hash'] = hash('sha256', Str::random(32));
        }

        return $data;
    }
}
