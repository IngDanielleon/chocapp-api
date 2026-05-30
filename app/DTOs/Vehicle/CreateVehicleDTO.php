<?php

namespace App\DTOs\Vehicle;

readonly class CreateVehicleDTO
{
    public function __construct(
        public string  $plate,
        public string  $brand,
        public string  $model,
        public int     $year,
        public string  $color,
        public string  $type,
        public ?string $photoUrl = null,
    ) {}

    public static function fromArray(array $data, ?string $photoUrl = null): self
    {
        return new self(
            plate:    strtoupper($data['plate']),
            brand:    $data['brand'],
            model:    $data['model'],
            year:     (int) $data['year'],
            color:    $data['color'],
            type:     $data['type'],
            photoUrl: $photoUrl,
        );
    }
}
