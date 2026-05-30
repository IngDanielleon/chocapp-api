<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Incident\CreateIncidentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\CreateIncidentRequest;
use App\Http\Requests\Incident\UpdateIncidentRequest;
use App\Http\Resources\IncidentDetailResource;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Models\ThirdParty;
use App\Services\IncidentService;
use App\Services\PdfReportService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Incidents", description="Gestión de accidentes de tránsito")
 */
class IncidentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly IncidentService  $incidentService,
        private readonly PdfReportService $pdfService,
    ) {}

    /**
     * @OA\Get(
     *   path="/incidents",
     *   tags={"Incidents"},
     *   summary="Listar accidentes del usuario autenticado",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="status", in="query", required=false,
     *     @OA\Schema(type="string", enum={"BORRADOR","REPORTADO","EN_REVISION","FINALIZADO"})
     *   ),
     *   @OA\Parameter(name="from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *   @OA\Response(response=200, description="Lista paginada de incidentes"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Incident::forUser(auth()->id())
            ->with(['vehicle', 'photos'])
            ->latest('incident_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('incident_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('incident_date', '<=', $request->to);
        }

        $incidents = $query->paginate($request->get('per_page', 15));

        return $this->successResponse(
            IncidentResource::collection($incidents),
            'Incidentes obtenidos exitosamente',
            200,
            $incidents
        );
    }

    /**
     * @OA\Post(
     *   path="/incidents",
     *   tags={"Incidents"},
     *   summary="Registrar un nuevo accidente con fotos",
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"vehicle_id","description","incident_date","incident_time","latitude","longitude","weather_condition","road_condition","photos"},
     *         @OA\Property(property="vehicle_id", type="string", format="uuid"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="incident_date", type="string", format="date"),
     *         @OA\Property(property="incident_time", type="string", example="14:30"),
     *         @OA\Property(property="location_address", type="string"),
     *         @OA\Property(property="latitude", type="number", format="float"),
     *         @OA\Property(property="longitude", type="number", format="float"),
     *         @OA\Property(property="weather_condition", type="string", enum={"SOLEADO","LLUVIOSO","NUBLADO","NOCHE"}),
     *         @OA\Property(property="road_condition", type="string", enum={"BUEN_ESTADO","HUMEDO","HUECOS","DERRUMBE"}),
     *         @OA\Property(property="police_report_number", type="string"),
     *         @OA\Property(property="photos", type="array",
     *           @OA\Items(
     *             @OA\Property(property="file", type="string", format="binary"),
     *             @OA\Property(property="angle", type="string")
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Incidente creado"),
     *   @OA\Response(response=422, description="Error de validación"),
     *   @OA\Response(response=429, description="Demasiadas solicitudes")
     * )
     */
    public function store(CreateIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->create(
            CreateIncidentDTO::fromRequest($request->validated()),
            $request->input('photos', []),
            auth()->user()
        );

        return $this->createdResponse(
            new IncidentDetailResource($incident),
            'Accidente registrado exitosamente'
        );
    }

    /**
     * @OA\Get(
     *   path="/incidents/{id}",
     *   tags={"Incidents"},
     *   summary="Detalle completo de un accidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Detalle del incidente"),
     *   @OA\Response(response=403, description="Acceso denegado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Incident $incident): JsonResponse
    {
        $this->authorize('view', $incident);
        $incident->load(['photos', 'thirdParties', 'vehicle', 'user']);

        return $this->successResponse(
            new IncidentDetailResource($incident),
            'Incidente obtenido exitosamente'
        );
    }

    /**
     * @OA\Put(
     *   path="/incidents/{id}",
     *   tags={"Incidents"},
     *   summary="Actualizar datos de un incidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Incidente actualizado")
     * )
     */
    public function update(UpdateIncidentRequest $request, Incident $incident): JsonResponse
    {
        $this->authorize('update', $incident);
        $incident->update($request->validated());

        return $this->successResponse(
            new IncidentDetailResource($incident->fresh(['photos', 'thirdParties', 'vehicle'])),
            'Incidente actualizado'
        );
    }

    /**
     * @OA\Delete(
     *   path="/incidents/{id}",
     *   tags={"Incidents"},
     *   summary="Eliminar un incidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Incident $incident): JsonResponse
    {
        $this->authorize('delete', $incident);
        $this->incidentService->deleteWithPhotos($incident);
        return $this->noContentResponse();
    }

    /**
     * @OA\Get(
     *   path="/incidents/{id}/export-pdf",
     *   tags={"Incidents"},
     *   summary="Generar y descargar reporte PDF oficial",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Archivo PDF", @OA\MediaType(mediaType="application/pdf"))
     * )
     */
    public function exportPdf(Incident $incident): Response
    {
        $this->authorize('view', $incident);
        $url = $this->pdfService->generate($incident);

        return response()->streamDownload(function () use ($url) {
            echo file_get_contents($url);
        }, "reporte-accidente-{$incident->id}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @OA\Post(
     *   path="/incidents/{id}/photos",
     *   tags={"Incidents"},
     *   summary="Agregar fotos a un incidente existente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Fotos agregadas")
     * )
     */
    public function addPhoto(Request $request, Incident $incident): JsonResponse
    {
        $this->authorize('update', $incident);

        $request->validate([
            'photos'         => 'required|array|min:1',
            'photos.*.file'  => 'required|image|mimes:jpeg,png,webp|max:10240',
            'photos.*.angle' => 'required|in:FRONT,FRONT_RIGHT,RIGHT,REAR_RIGHT,REAR,REAR_LEFT,LEFT,FRONT_LEFT,INTERIOR,ODOMETER,EXTRA',
        ]);

        $this->incidentService->uploadPhotos($incident, $request->input('photos', []));

        return $this->successResponse(null, 'Fotos agregadas exitosamente');
    }

    /**
     * @OA\Delete(
     *   path="/incidents/{id}/photos/{photo_id}",
     *   tags={"Incidents"},
     *   summary="Eliminar una foto de un incidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Parameter(name="photo_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminada")
     * )
     */
    public function removePhoto(Incident $incident, string $photoId): JsonResponse
    {
        $this->authorize('update', $incident);
        $photo = $incident->photos()->findOrFail($photoId);
        $photo->delete();
        return $this->noContentResponse();
    }

    /**
     * @OA\Post(
     *   path="/incidents/{id}/third-parties",
     *   tags={"Incidents"},
     *   summary="Agregar tercero involucrado en el accidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=201, description="Tercero agregado")
     * )
     */
    public function addThirdParty(Request $request, Incident $incident): JsonResponse
    {
        $this->authorize('update', $incident);

        $validated = $request->validate([
            'party_type'       => 'required|in:VEHICULO,PEATON,CICLISTA',
            'plate'            => 'nullable|string|max:10',
            'brand'            => 'nullable|string|max:60',
            'model'            => 'nullable|string|max:60',
            'color'            => 'nullable|string|max:40',
            'driver_name'      => 'nullable|string|max:100',
            'driver_id'        => 'nullable|string|max:30',
            'driver_phone'     => 'nullable|string|max:20',
            'insurance_company'=> 'nullable|string|max:100',
            'insurance_policy' => 'nullable|string|max:60',
        ]);

        $thirdParty = $incident->thirdParties()->create($validated);

        return $this->createdResponse($thirdParty, 'Tercero agregado exitosamente');
    }
}
