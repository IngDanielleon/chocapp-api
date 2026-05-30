<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'id_type'         => $this->id_type,
            'id_number'       => $this->id_number,
            'phone_number'    => $this->phone_number,
            'profile_pic_url' => $this->profile_pic_url,
            'terms_accepted'  => $this->terms_accepted,
            'vehicles_count'  => $this->whenLoaded('vehicles', fn() => $this->vehicles->count(), 0),
            'vehicles'        => VehicleResource::collection($this->whenLoaded('vehicles')),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
