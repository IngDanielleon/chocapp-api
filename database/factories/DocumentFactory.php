<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id'      => Vehicle::factory(),
            'type'            => $this->faker->randomElement(['SOAT', 'TECNOMECANICA', 'LICENCIA']),
            'document_number' => $this->faker->numerify('##########'),
            'issue_date'      => now()->subYear()->toDateString(),
            'expiry_date'     => now()->addYear()->toDateString(),
        ];
    }
}
