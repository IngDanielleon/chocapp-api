<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed  $data    = null,
        string $message = 'Operación exitosa',
        int    $status  = 200,
        ?LengthAwarePaginator $paginator = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if ($paginator) {
            $response['meta'] = [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ];
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(
        string $message = 'Ha ocurrido un error',
        int    $status  = 400,
        mixed  $errors  = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'code'    => $status,
        ], $status);
    }

    protected function createdResponse(mixed $data, string $message = 'Creado exitosamente'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
