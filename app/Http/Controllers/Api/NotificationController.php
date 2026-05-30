<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Notifications", description="Notificaciones del usuario")
 */
class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *   path="/notifications",
     *   tags={"Notifications"},
     *   summary="Listar notificaciones del usuario",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="unread_only", in="query", @OA\Schema(type="boolean")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *   @OA\Response(response=200, description="Lista de notificaciones")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->notifications()->latest();

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Notificaciones obtenidas',
            200,
            $notifications
        );
    }

    /**
     * @OA\Patch(
     *   path="/notifications/{id}/read",
     *   tags={"Notifications"},
     *   summary="Marcar una notificación como leída",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=200, description="Marcada como leída")
     * )
     */
    public function markRead(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return $this->successResponse(null, 'Notificación marcada como leída');
    }

    /**
     * @OA\Patch(
     *   path="/notifications/read-all",
     *   tags={"Notifications"},
     *   summary="Marcar todas las notificaciones como leídas",
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Todas marcadas como leídas")
     * )
     */
    public function markAllRead(): JsonResponse
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse(null, 'Todas las notificaciones marcadas como leídas');
    }

    /**
     * @OA\Delete(
     *   path="/notifications/{id}",
     *   tags={"Notifications"},
     *   summary="Eliminar una notificación",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *   @OA\Response(response=204, description="Eliminada")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();
        return $this->noContentResponse();
    }
}
