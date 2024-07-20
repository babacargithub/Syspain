<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Intrant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class IntrantFactory extends Factory
{
    protected $model = Intrant::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->word(),
        ];
    }
    public function configure(): IntrantFactory
    {
        return $this->afterCreating(function (Intrant $intrant) {
            $intrant->stock()->create([
                "boulangerie_id" => $intrant->boulangerie_id,
                "quantite" => 100,
                "code_bar" => $this->faker->unique()->randomNumber(9),
                "prix_achat" => 0,
                "nom" => "Stock de ".$intrant->nom,

            ]);
        });
    }
}
