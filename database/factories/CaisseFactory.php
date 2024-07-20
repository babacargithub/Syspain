<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Caisse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caisse>
 */
class CaisseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $definition = [
            "nom" => "Caisse Principale",

            //
        ];
        // if env is testing, we can add the following fields
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory();
        }
        return $definition;
    }
}
