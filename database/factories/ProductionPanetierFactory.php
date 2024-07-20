<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\ProductionPanetier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductionPanetierFactory extends Factory
{
    protected $model = ProductionPanetier::class;

    public function definition(): array
    {
        $definition= [
            'date_production' => today()->toDateString(),
            'nombre_pain' => $this->faker->numberBetween(1000,10000),
            'nombre_plat' => $this->faker->numberBetween(100,1000),
            'nombre_sac' => $this->faker->numberBetween(1,100),
            'ration' => $this->faker->numberBetween(10,100),
            'donation' => $this->faker->numberBetween(10,100),
            'casse' => $this->faker->numberBetween(10,100),
            'mange' => $this->faker->numberBetween(10,100),
            'periode' => $this->faker->randomElement(['matin', 'soir']),

        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory();
        }
        return $definition;
    }
}
