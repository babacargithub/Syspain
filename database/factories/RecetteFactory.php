<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\Recette;
use App\Models\TypeRecette;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recette>
 */
class RecetteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "montant" => $this->faker->randomNumber(7),
            "type_recette_id" => TypeRecette::factory(),
            "boulangerie_id" => Boulangerie::factory(),
        ];
    }
}
