<?php

namespace App\Enums;

enum IncidentStatusEnum: string
{
    case BORRADOR    = 'BORRADOR';
    case REPORTADO   = 'REPORTADO';
    case EN_REVISION = 'EN_REVISION';
    case FINALIZADO  = 'FINALIZADO';

    public function label(): string
    {
        return match($this) {
            self::BORRADOR    => 'Borrador',
            self::REPORTADO   => 'Reportado',
            self::EN_REVISION => 'En revisión',
            self::FINALIZADO  => 'Finalizado',
        };
    }
}
