<?php

namespace App\DTOs\Incident;

readonly class CreateIncidentDTO
{
    public function __construct(
        public string  $vehicleId,
        public string  $title,
        public string  $description,
        public string  $incidentDate,
        public string  $incidentTime,
        public string  $locationAddress,
        public float   $latitude,
        public float   $longitude,
        public string  $weatherCondition,
        public string  $roadCondition,
        public ?string $policeReportNumber = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            vehicleId:          $data['vehicle_id'],
            title:              $data['title'] ?? 'Accidente ' . now()->format('d/m/Y'),
            description:        $data['description'],
            incidentDate:       $data['incident_date'],
            incidentTime:       $data['incident_time'],
            locationAddress:    $data['location_address'] ?? '',
            latitude:           (float) $data['latitude'],
            longitude:          (float) $data['longitude'],
            weatherCondition:   $data['weather_condition'],
            roadCondition:      $data['road_condition'],
            policeReportNumber: $data['police_report_number'] ?? null,
        );
    }
}
