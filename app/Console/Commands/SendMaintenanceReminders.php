<?php

namespace App\Console\Commands;

use App\Models\MaintenanceRecord;
use App\Services\FcmNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMaintenanceReminders extends Command
{
    protected $signature   = 'chocapp:maintenance-reminders';
    protected $description = 'Enviar recordatorios push de mantenimientos próximos';

    public function __construct(private readonly FcmNotificationService $fcm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $upcoming = MaintenanceRecord::with('vehicle.user')
            ->whereNotNull('next_date')
            ->whereBetween('next_date', [
                now()->toDateString(),
                now()->addDays(7)->toDateString(),
            ])
            ->get();

        $count = 0;
        foreach ($upcoming as $record) {
            $user = $record->vehicle?->user;
            if (!$user) {
                continue;
            }

            $this->fcm->send(
                $user,
                'Recordatorio de mantenimiento',
                "Tienes un mantenimiento de {$record->type} programado para el {$record->next_date->format('d/m/Y')}.",
                [
                    'type'                => 'MAINTENANCE_REMINDER',
                    'maintenance_record_id' => $record->id,
                ]
            );

            $count++;
        }

        Log::channel('daily')->info("ChocApp: Recordatorios de mantenimiento enviados: {$count}");
        $this->info("Recordatorios enviados: {$count}");

        return Command::SUCCESS;
    }
}
