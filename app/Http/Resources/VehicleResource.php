<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'plate'     => $this->plate,
            'brand'     => $this->brand,
            'model'     => $this->model,
            'year'      => $this->year,
            'color'     => $this->color,
            'type'      => $this->type instanceof \App\Enums\VehicleTypeEnum
                ? $this->type->value
                : $this->type,
            'photo_url' => $this->photo_url,
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'created_at'=> $this->created_at->toIso8601String(),
        ];
    }
}
