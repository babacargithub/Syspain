<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeRecette>
 */
class TypeRecetteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $definition = [
            'nom' => $this->faker->word,


        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory();
        }
        return $definition;
    }
}
