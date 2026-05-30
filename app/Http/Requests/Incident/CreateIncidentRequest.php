<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;

class CreateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'           => 'required|uuid|exists:vehicles,id',
            'title'                => 'nullable|string|max:200',
            'description'          => 'required|string|min:10|max:2000',
            'incident_date'        => 'required|date|before_or_equal:today',
            'incident_time'        => 'required|date_format:H:i',
            'location_address'     => 'nullable|string|max:500',
            'latitude'             => 'required|numeric|between:-90,90',
            'longitude'            => 'required|numeric|between:-180,180',
            'weather_condition'    => 'required|in:SOLEADO,LLUVIOSO,NUBLADO,NOCHE',
            'road_condition'       => 'required|in:BUEN_ESTADO,HUMEDO,HUECOS,DERRUMBE',
            'police_report_number' => 'nullable|string|max:60',
            'photos'               => 'required|array|min:4',
            'photos.*.file'        => 'required|image|mimes:jpeg,png,webp|max:10240',
            'photos.*.angle'       => 'required|in:FRONT,FRONT_RIGHT,RIGHT,REAR_RIGHT,REAR,REAR_LEFT,LEFT,FRONT_LEFT,INTERIOR,ODOMETER,EXTRA',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $vehicle = \App\Models\Vehicle::find($this->vehicle_id);
            if ($vehicle && $vehicle->user_id !== auth()->id()) {
                $v->errors()->add('vehicle_id', 'El vehículo no pertenece al usuario autenticado.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'vehicle_id'       => 'vehículo',
            'description'      => 'descripción',
            'incident_date'    => 'fecha del incidente',
            'incident_time'    => 'hora del incidente',
            'location_address' => 'dirección',
            'latitude'         => 'latitud',
            'longitude'        => 'longitud',
            'photos'           => 'fotografías',
            'photos.*.file'    => 'archivo de fotografía',
            'photos.*.angle'   => 'ángulo de fotografía',
        ];
    }
}
