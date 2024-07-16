<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Caisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caisse>
 */
class ChariotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "nom" => "Chariot ".$this->faker->numberBetween(100, 500)." pains ",
            "nombre_pain" => $this->faker->numberBetween(100, 500),

            //
        ];
    }
}
