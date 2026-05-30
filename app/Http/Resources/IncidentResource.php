<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'status'           => $this->status instanceof \App\Enums\IncidentStatusEnum
                ? $this->status->value
                : $this->status,
            'status_label'     => $this->status instanceof \App\Enums\IncidentStatusEnum
                ? $this->status->label()
                : $this->status,
            'incident_date'    => $this->incident_date->format('Y-m-d'),
            'incident_time'    => $this->incident_time,
            'location_address' => $this->location_address,
            'latitude'         => (float) $this->latitude,
            'longitude'        => (float) $this->longitude,
            'cover_photo_url'  => $this->cover_photo_url,
            'photos_count'     => $this->whenLoaded('photos', fn() => $this->photos->count(), 0),
            'vehicle'          => new VehicleResource($this->whenLoaded('vehicle')),
            'has_pdf'          => !empty($this->report_pdf_url),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
