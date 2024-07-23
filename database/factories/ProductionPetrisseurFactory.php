<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class ProductionPetrisseurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $definition = [
            "date_production" => $this->faker->date(),
            "nombre_chariot" => $this->faker->numberBetween(1, 100),
            "nombre_pain" => $this->faker->numberBetween(3000, 9000),
            "nombre_plat" => $this->faker->numberBetween(1, 100),
            "nombre_sac" => $this->faker->numberBetween(1, 30),

            //
        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory()::mockActiveBoulangerie()->id;
        }
        return $definition;
    }
}
