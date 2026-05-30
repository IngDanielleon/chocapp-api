<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceResource;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Maintenance", description="Historial de mantenimiento de vehículos")
 */
class MaintenanceController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *   path="/vehicles/{vehicle_id}/maintenance",
     *   tags={"Maintenance"},
     *   summary="Listar registros de mantenimiento de un vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Historial de mantenimiento")
     * )
     */
    public function index(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);
        $records = $vehicle->maintenanceRecords()->latest('maintenance_date')->get();
        return $this->successResponse(MaintenanceResource::collection($records), 'Mantenimientos obtenidos');
    }

    /**
     * @OA\Post(
     *   path="/vehicles/{vehicle_id}/maintenance",
     *   tags={"Maintenance"},
     *   summary="Registrar un mantenimiento",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=201, description="Mantenimiento registrado")
     * )
     */
    public function store(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'type'             => 'required|in:ACEITE,FRENOS,LLANTAS,BATERIA,FILTROS,SUSPENSION,REVISION_GENERAL,OTRO',
            'cost'             => 'nullable|numeric|min:0',
            'workshop_name'    => 'nullable|string|max:150',
            'current_mileage'  => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:2000',
            'next_date'        => 'nullable|date|after:maintenance_date',
            'next_mileage'     => 'nullable|integer|min:0',
        ]);

        $record = $vehicle->maintenanceRecords()->create($validated);

        return $this->createdResponse(new MaintenanceResource($record), 'Mantenimiento registrado exitosamente');
    }

    /**
     * @OA\Put(
     *   path="/vehicles/{vehicle_id}/maintenance/{record_id}",
     *   tags={"Maintenance"},
     *   summary="Actualizar un registro de mantenimiento",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Parameter(name="record_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Mantenimiento actualizado")
     * )
     */
    public function update(Request $request, Vehicle $vehicle, MaintenanceRecord $record): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'maintenance_date' => 'nullable|date',
            'type'             => 'nullable|in:ACEITE,FRENOS,LLANTAS,BATERIA,FILTROS,SUSPENSION,REVISION_GENERAL,OTRO',
            'cost'             => 'nullable|numeric|min:0',
            'workshop_name'    => 'nullable|string|max:150',
            'current_mileage'  => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:2000',
            'next_date'        => 'nullable|date',
            'next_mileage'     => 'nullable|integer|min:0',
        ]);

        $record->update(array_filter($validated, fn($v) => !is_null($v)));

        return $this->successResponse(new MaintenanceResource($record->fresh()), 'Mantenimiento actualizado');
    }

    /**
     * @OA\Delete(
     *   path="/vehicles/{vehicle_id}/maintenance/{record_id}",
     *   tags={"Maintenance"},
     *   summary="Eliminar un registro de mantenimiento",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Parameter(name="record_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Vehicle $vehicle, MaintenanceRecord $record): JsonResponse
    {
        $this->authorize('update', $vehicle);
        $record->delete();
        return $this->noContentResponse();
    }
}
