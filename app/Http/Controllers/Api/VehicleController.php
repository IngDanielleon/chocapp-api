<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\CreateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\StorageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Vehicles", description="Gestión de vehículos del usuario")
 */
class VehicleController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly StorageService $storageService) {}

    /**
     * @OA\Get(
     *   path="/vehicles",
     *   tags={"Vehicles"},
     *   summary="Listar vehículos del usuario autenticado",
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Lista de vehículos")
     * )
     */
    public function index(): JsonResponse
    {
        $vehicles = auth()->user()->vehicles()->with('documents')->get();
        return $this->successResponse(VehicleResource::collection($vehicles), 'Vehículos obtenidos');
    }

    /**
     * @OA\Post(
     *   path="/vehicles",
     *   tags={"Vehicles"},
     *   summary="Registrar un nuevo vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"plate","brand","model","year","color","type"},
     *         @OA\Property(property="plate", type="string"),
     *         @OA\Property(property="brand", type="string"),
     *         @OA\Property(property="model", type="string"),
     *         @OA\Property(property="year", type="integer"),
     *         @OA\Property(property="color", type="string"),
     *         @OA\Property(property="type", type="string", enum={"MOTOCICLETA","AUTOMOVIL"}),
     *         @OA\Property(property="photo", type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Vehículo creado"),
     *   @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(CreateVehicleRequest $request): JsonResponse
    {
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $this->storageService->uploadFile($request->file('photo'), 'vehicles/photos');
        }

        $vehicle = auth()->user()->vehicles()->create([
            ...$request->validated(),
            'plate'     => strtoupper($request->plate),
            'photo_url' => $photoUrl,
        ]);

        return $this->createdResponse(new VehicleResource($vehicle), 'Vehículo registrado exitosamente');
    }

    /**
     * @OA\Get(
     *   path="/vehicles/{id}",
     *   tags={"Vehicles"},
     *   summary="Detalle de un vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Detalle del vehículo"),
     *   @OA\Response(response=403, description="Acceso denegado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);
        $vehicle->load(['documents', 'maintenanceRecords']);
        return $this->successResponse(new VehicleResource($vehicle), 'Vehículo obtenido');
    }

    /**
     * @OA\Put(
     *   path="/vehicles/{id}",
     *   tags={"Vehicles"},
     *   summary="Actualizar datos de un vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Vehículo actualizado")
     * )
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'brand' => 'nullable|string|max:60',
            'model' => 'nullable|string|max:60',
            'year'  => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:40',
            'type'  => 'nullable|in:MOTOCICLETA,AUTOMOVIL',
            'photo' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = $this->storageService->uploadFile($request->file('photo'), 'vehicles/photos');
        }

        $vehicle->update(array_filter($validated, fn($v) => !is_null($v)));

        return $this->successResponse(new VehicleResource($vehicle->fresh()), 'Vehículo actualizado');
    }

    /**
     * @OA\Delete(
     *   path="/vehicles/{id}",
     *   tags={"Vehicles"},
     *   summary="Eliminar un vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);
        $vehicle->delete();
        return $this->noContentResponse();
    }
}
