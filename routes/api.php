<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

// ── Rutas públicas ────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('auth/register',        [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('auth/login',           [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('auth/social',          [AuthController::class, 'social'])->middleware('throttle:auth');
    Route::post('auth/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('auth/password/reset',  [AuthController::class, 'resetPassword']);
});

// ── Rutas protegidas ──────────────────────────────────────────────────────────
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'force.json', 'security.headers'])
    ->group(function () {

        // Auth
        Route::post('auth/logout',  [AuthController::class, 'logout']);
        Route::get('auth/me',       [AuthController::class, 'me']);
        Route::put('auth/profile',  [AuthController::class, 'updateProfile']);

        // Vehículos
        Route::apiResource('vehicles', VehicleController::class);

        // Documentos — anidados bajo vehículo
        Route::prefix('vehicles/{vehicle}')->group(function () {
            Route::get('documents',               [DocumentController::class,    'index']);
            Route::post('documents',              [DocumentController::class,    'upsert']);
            Route::delete('documents/{document}', [DocumentController::class,    'destroy']);

            // Mantenimiento — anidado bajo vehículo
            Route::get('maintenance',             [MaintenanceController::class, 'index']);
            Route::post('maintenance',            [MaintenanceController::class, 'store']);
            Route::put('maintenance/{record}',    [MaintenanceController::class, 'update']);
            Route::delete('maintenance/{record}', [MaintenanceController::class, 'destroy']);
        });

        // Incidentes
        Route::apiResource('incidents', IncidentController::class);
        Route::get('incidents/{incident}/export-pdf',          [IncidentController::class, 'exportPdf'])
             ->middleware('throttle:heavy');
        Route::post('incidents/{incident}/photos',             [IncidentController::class, 'addPhoto'])
             ->middleware('throttle:heavy');
        Route::delete('incidents/{incident}/photos/{photo}',   [IncidentController::class, 'removePhoto']);
        Route::post('incidents/{incident}/third-parties',      [IncidentController::class, 'addThirdParty']);

        // Notificaciones
        Route::get('notifications',                  [NotificationController::class, 'index']);
        Route::patch('notifications/read-all',        [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{id}/read',       [NotificationController::class, 'markRead']);
        Route::delete('notifications/{id}',           [NotificationController::class, 'destroy']);

        // Soporte
        Route::get('support/emergency-contacts', [SupportController::class, 'emergencyContacts']);
        Route::get('support/workshops',          [SupportController::class, 'workshops']);
    });
