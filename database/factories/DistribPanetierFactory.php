<?php

namespace Database\Factories;

use App\Models\DistribPanetier;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termwind\Components\Li;

class DistribPanetierFactory extends Factory
{
    protected $model = DistribPanetier::class;

    public function definition(): array
    {
        $definition = [
            "livreur_id" => Livreur::factory(),
            "nombre_pain" => $this->faker->numberBetween(100, 1000)
        ];
         if (app()->environment('testing')) {
             $definition["production_panetier_id"] =  ProductionPanetier::factory();
         }
         return $definition;
    }

}
