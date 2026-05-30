<?php

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case SOAT          = 'SOAT';
    case TECNOMECANICA = 'TECNOMECANICA';
    case LICENCIA      = 'LICENCIA';

    public function label(): string
    {
        return match($this) {
            self::SOAT          => 'SOAT',
            self::TECNOMECANICA => 'Tecnomecánica',
            self::LICENCIA      => 'Licencia de conducción',
        };
    }
}
