<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'title'                => $this->title,
            'description'          => $this->description,
            'status'               => $this->status instanceof \App\Enums\IncidentStatusEnum
                ? $this->status->value
                : $this->status,
            'status_label'         => $this->status instanceof \App\Enums\IncidentStatusEnum
                ? $this->status->label()
                : $this->status,
            'incident_date'        => $this->incident_date->format('Y-m-d'),
            'incident_time'        => $this->incident_time,
            'location_address'     => $this->location_address,
            'latitude'             => (float) $this->latitude,
            'longitude'            => (float) $this->longitude,
            'weather_condition'    => $this->weather_condition,
            'road_condition'       => $this->road_condition,
            'police_report_number' => $this->police_report_number,
            'cover_photo_url'      => $this->cover_photo_url,
            'has_pdf'              => !empty($this->report_pdf_url),
            'report_pdf_url'       => $this->report_pdf_url,
            'vehicle'              => new VehicleResource($this->whenLoaded('vehicle')),
            'user'                 => new UserResource($this->whenLoaded('user')),
            'photos'               => $this->whenLoaded('photos', function () {
                return $this->photos->map(fn($p) => [
                    'id'        => $p->id,
                    'angle'     => $p->angle,
                    'image_url' => $p->image_url,
                    'taken_at'  => $p->taken_at?->toIso8601String(),
                ]);
            }),
            'third_parties' => $this->whenLoaded('thirdParties', function () {
                return $this->thirdParties->map(fn($tp) => [
                    'id'                => $tp->id,
                    'party_type'        => $tp->party_type,
                    'plate'             => $tp->plate,
                    'brand'             => $tp->brand,
                    'model'             => $tp->model,
                    'color'             => $tp->color,
                    'driver_name'       => $tp->driver_name,
                    'driver_id'         => $tp->driver_id,
                    'driver_phone'      => $tp->driver_phone,
                    'insurance_company' => $tp->insurance_company,
                    'insurance_policy'  => $tp->insurance_policy,
                ]);
            }),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
