<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Livreur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LivreurFactory extends Factory
{
    protected $model = Livreur::class;

    public function definition(): array
    {
        $definition = [
            'prenom' => $this->faker->firstName(),
            'nom' => $this->faker->lastName(),
            'telephone' => '77' . $this->faker->unique()->randomNumber(7),
        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory()::mockActiveBoulangerie()->id;
        }
        return $definition;
    }
    // add compte livreur
    public function configure(): LivreurFactory
    {
        return $this->afterCreating(function (Livreur $livreur) {
            $livreur->compteLivreur()->create([
                'solde_pain' => 0,
                'dette' => 0,
                'solde_reliquat' => 0,
            ]);
        });
    }
}
