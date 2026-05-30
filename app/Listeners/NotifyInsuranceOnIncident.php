<?php

namespace App\Listeners;

use App\Events\IncidentCreated;
use App\Services\FcmNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyInsuranceOnIncident implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(private readonly FcmNotificationService $fcm) {}

    public function handle(IncidentCreated $event): void
    {
        $incident = $event->incident;
        $user     = $incident->user;

        if (!$user) {
            return;
        }

        $this->fcm->send(
            $user,
            'Accidente registrado',
            "Tu accidente del {$incident->incident_date->format('d/m/Y')} ha sido reportado exitosamente.",
            [
                'type'        => 'INCIDENT_UPDATE',
                'incident_id' => $incident->id,
            ]
        );
    }
}
