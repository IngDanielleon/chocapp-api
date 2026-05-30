<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── ChocApp Scheduled Tasks ───────────────────────────────────────────────────

// Alertas de documentos próximos a vencer — todos los días a las 8AM
Schedule::command('chocapp:document-expiry-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Recordatorios de mantenimiento — todos los lunes a las 9AM
Schedule::command('chocapp:maintenance-reminders')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping();

// Limpiar tokens Sanctum expirados (90 días = 2160 horas)
Schedule::command('sanctum:prune-expired --hours=2160')
    ->daily();

// Limpiar jobs fallidos con más de 7 días
Schedule::command('queue:prune-failed --hours=168')
    ->weekly();
