<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate'     => 'required|string|max:10|unique:vehicles,plate',
            'brand'     => 'required|string|max:60',
            'model'     => 'required|string|max:60',
            'year'      => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color'     => 'required|string|max:40',
            'type'      => 'required|in:MOTOCICLETA,AUTOMOVIL',
            'photo'     => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'plate' => 'placa',
            'brand' => 'marca',
            'model' => 'modelo',
            'year'  => 'año',
            'color' => 'color',
            'type'  => 'tipo de vehículo',
        ];
    }
}
