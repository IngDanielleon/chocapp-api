<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'maintenance_date' => $this->maintenance_date->format('Y-m-d'),
            'type'             => $this->type,
            'cost'             => $this->cost ? (float) $this->cost : null,
            'workshop_name'    => $this->workshop_name,
            'current_mileage'  => $this->current_mileage,
            'notes'            => $this->notes,
            'next_date'        => $this->next_date?->format('Y-m-d'),
            'next_mileage'     => $this->next_mileage,
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
