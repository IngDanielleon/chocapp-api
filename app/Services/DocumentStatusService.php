<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class DocumentStatusService
{
    public function getVehicleDocuments(Vehicle $vehicle): Collection
    {
        return $vehicle->documents()->get();
    }

    public function upsert(Vehicle $vehicle, array $data, ?string $pdfUrl = null): Document
    {
        $attributes = ['vehicle_id' => $vehicle->id, 'type' => $data['type']];

        $values = [
            'document_number' => $data['document_number'],
            'issue_date'      => $data['issue_date'] ?? null,
            'expiry_date'     => $data['expiry_date'],
            'notes'           => $data['notes'] ?? null,
        ];

        if ($pdfUrl) {
            $values['pdf_url'] = $pdfUrl;
        }

        return Document::updateOrCreate($attributes, $values);
    }
}
