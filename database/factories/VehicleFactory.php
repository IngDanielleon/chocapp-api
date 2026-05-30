<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plate'   => strtoupper($this->faker->bothify('???###')),
            'brand'   => $this->faker->randomElement(['Chevrolet', 'Renault', 'Mazda', 'Toyota', 'Yamaha', 'Honda']),
            'model'   => $this->faker->randomElement(['Sail', 'Logan', '3', 'Corolla', 'FZ', 'CB190']),
            'year'    => $this->faker->numberBetween(2010, 2024),
            'color'   => $this->faker->colorName(),
            'type'    => $this->faker->randomElement(['MOTOCICLETA', 'AUTOMOVIL']),
        ];
    }
}
