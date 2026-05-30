<?php

namespace Database\Factories;

use App\Enums\IncidentStatusEnum;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'vehicle_id'       => Vehicle::factory(),
            'title'            => 'Accidente ' . $this->faker->date('d/m/Y'),
            'description'      => $this->faker->paragraph(3),
            'incident_date'    => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'incident_time'    => $this->faker->time('H:i'),
            'location_address' => $this->faker->address(),
            'latitude'         => $this->faker->latitude(4.0, 5.5),
            'longitude'        => $this->faker->longitude(-76.0, -73.0),
            'weather_condition'=> $this->faker->randomElement(['SOLEADO', 'LLUVIOSO', 'NUBLADO', 'NOCHE']),
            'road_condition'   => $this->faker->randomElement(['BUEN_ESTADO', 'HUMEDO', 'HUECOS', 'DERRUMBE']),
            'status'           => IncidentStatusEnum::REPORTADO->value,
        ];
    }
}
