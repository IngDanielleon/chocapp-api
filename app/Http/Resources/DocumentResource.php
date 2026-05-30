<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type instanceof \App\Enums\DocumentTypeEnum
                ? $this->type->value
                : $this->type,
            'document_number' => $this->document_number,
            'issue_date'      => $this->issue_date?->format('Y-m-d'),
            'expiry_date'     => $this->expiry_date->format('Y-m-d'),
            'status'          => $this->status,
            'days_remaining'  => (int) now()->diffInDays($this->expiry_date, false),
            'has_pdf'         => !empty($this->pdf_url),
            'notes'           => $this->notes,
            'updated_at'      => $this->updated_at->toIso8601String(),
        ];
    }
}
