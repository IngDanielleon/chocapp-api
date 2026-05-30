<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Support", description="Información de soporte y emergencias")
 */
class SupportController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *   path="/support/emergency-contacts",
     *   tags={"Support"},
     *   summary="Contactos de emergencia Colombia",
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Lista de contactos de emergencia")
     * )
     */
    public function emergencyContacts(): JsonResponse
    {
        $contacts = [
            ['name' => 'Emergencias Nacional', 'phone' => '123', 'type' => 'EMERGENCY'],
            ['name' => 'Policía Nacional',     'phone' => '112', 'type' => 'POLICE'],
            ['name' => 'Cruz Roja Colombia',   'phone' => '132', 'type' => 'AMBULANCE'],
            ['name' => 'Bomberos',             'phone' => '119', 'type' => 'FIRE'],
            ['name' => 'Tránsito Bogotá',      'phone' => '3153150114', 'type' => 'TRANSIT'],
            ['name' => 'Tránsito Medellín',    'phone' => '3147882040', 'type' => 'TRANSIT'],
            ['name' => 'Grúas Cali',           'phone' => '3004567890', 'type' => 'TOW_TRUCK'],
            ['name' => 'Abogados Tránsito 24h','phone' => '3001234567', 'type' => 'LEGAL'],
        ];

        return $this->successResponse($contacts, 'Contactos de emergencia obtenidos');
    }

    /**
     * @OA\Get(
     *   path="/support/workshops",
     *   tags={"Support"},
     *   summary="Talleres cercanos a una coordenada",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="number")),
     *   @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="number")),
     *   @OA\Parameter(name="radius_km", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *   @OA\Response(response=200, description="Lista de talleres cercanos")
     * )
     */
    public function workshops(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:50',
        ]);

        // Stub data — integrate with Google Places API in production
        $workshops = [
            [
                'name'      => 'Taller Hernández',
                'address'   => 'Calle 72 # 45-12, Bogotá',
                'phone'     => '6014567890',
                'latitude'  => 4.6800,
                'longitude' => -74.0560,
                'distance_km' => 1.2,
            ],
            [
                'name'      => 'AutoServicio El Rápido',
                'address'   => 'Av. El Dorado # 68B-31, Bogotá',
                'phone'     => '6013456789',
                'latitude'  => 4.6750,
                'longitude' => -74.0600,
                'distance_km' => 2.5,
            ],
        ];

        return $this->successResponse($workshops, 'Talleres obtenidos');
    }
}
