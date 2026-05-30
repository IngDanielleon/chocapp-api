<?php

namespace App\Services;

use App\Models\Incident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\File;
use Illuminate\Support\Str;

class PdfReportService
{
    public function __construct(
        private readonly StorageService $storageService,
    ) {}

    public function generate(Incident $incident): string
    {
        if ($incident->report_pdf_url) {
            return $incident->report_pdf_url;
        }

        $incident->load(['photos', 'thirdParties', 'vehicle', 'user']);

        $pdf = Pdf::loadView('pdf.incident-report', [
            'incident'    => $incident,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $tmpPath = sys_get_temp_dir() . '/' . Str::uuid() . '.pdf';
        file_put_contents($tmpPath, $pdf->output());

        $url = $this->storageService->uploadFile(
            new File($tmpPath),
            "incidents/{$incident->id}/reports"
        );

        @unlink($tmpPath);

        $incident->update(['report_pdf_url' => $url]);

        return $url;
    }
}
