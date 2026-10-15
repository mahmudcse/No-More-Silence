<?php

declare(strict_types=1);

namespace App\Actions\Export;

use App\Enums\WorkflowStatus;
use App\Models\FacebookProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Symfony\Component\HttpFoundation\Response;

class ExportEvidencePackage
{
    public function makePdf(FacebookProfile $profile): DomPdfWrapper
    {
        $profile->loadMissing('district');

        $reports = $profile->reports()
            ->where('workflow_status', WorkflowStatus::Verified)
            ->with(['incident.district', 'media'])
            ->latest('verified_at')
            ->get();

        return Pdf::loadView('pdf.evidence-package', [
            'profile' => $profile,
            'reports' => $reports,
        ])->setPaper('a4', 'portrait');
    }

    public function download(FacebookProfile $profile): Response
    {
        $pdf = $this->makePdf($profile);
        $filename = sprintf('evidence-dossier-%s.pdf', substr($profile->uuid, 0, 8));

        return $pdf->download($filename);
    }

    public function stream(FacebookProfile $profile): Response
    {
        $pdf = $this->makePdf($profile);
        $filename = sprintf('evidence-dossier-%s.pdf', substr($profile->uuid, 0, 8));

        return $pdf->stream($filename);
    }
}
