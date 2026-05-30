<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Document\UpsertDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Vehicle;
use App\Services\DocumentStatusService;
use App\Services\StorageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Documents", description="Documentos de vehículos (SOAT, Tecnomecánica, Licencia)")
 */
class DocumentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly DocumentStatusService $documentService,
        private readonly StorageService        $storageService,
    ) {}

    /**
     * @OA\Get(
     *   path="/vehicles/{vehicle_id}/documents",
     *   tags={"Documents"},
     *   summary="Listar documentos de un vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Documentos del vehículo")
     * )
     */
    public function index(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);
        $documents = $this->documentService->getVehicleDocuments($vehicle);
        return $this->successResponse(DocumentResource::collection($documents), 'Documentos obtenidos');
    }

    /**
     * @OA\Post(
     *   path="/vehicles/{vehicle_id}/documents",
     *   tags={"Documents"},
     *   summary="Crear o actualizar un documento de vehículo",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"type","document_number","expiry_date"},
     *         @OA\Property(property="type", type="string", enum={"SOAT","TECNOMECANICA","LICENCIA"}),
     *         @OA\Property(property="document_number", type="string"),
     *         @OA\Property(property="issue_date", type="string", format="date"),
     *         @OA\Property(property="expiry_date", type="string", format="date"),
     *         @OA\Property(property="notes", type="string"),
     *         @OA\Property(property="pdf_file", type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="Documento guardado")
     * )
     */
    public function upsert(UpsertDocumentRequest $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $pdfUrl = null;
        if ($request->hasFile('pdf_file')) {
            $pdfUrl = $this->storageService->uploadFile(
                $request->file('pdf_file'),
                "vehicles/{$vehicle->id}/documents"
            );
        }

        $document = $this->documentService->upsert($vehicle, $request->validated(), $pdfUrl);

        return $this->successResponse(new DocumentResource($document), 'Documento guardado exitosamente');
    }

    /**
     * @OA\Delete(
     *   path="/vehicles/{vehicle_id}/documents/{document_id}",
     *   tags={"Documents"},
     *   summary="Eliminar un documento",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="vehicle_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Parameter(name="document_id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminado")
     * )
     */
    public function destroy(Vehicle $vehicle, Document $document): JsonResponse
    {
        $this->authorize('update', $vehicle);
        $document->delete();
        return $this->noContentResponse();
    }
}
