<?php

namespace App\Enums;

enum VehicleTypeEnum: string
{
    case MOTOCICLETA = 'MOTOCICLETA';
    case AUTOMOVIL   = 'AUTOMOVIL';

    public function label(): string
    {
        return match($this) {
            self::MOTOCICLETA => 'Motocicleta',
            self::AUTOMOVIL   => 'Automóvil',
        };
    }
}
