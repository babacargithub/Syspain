<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\CompteLivreur;
use App\Models\Livreur;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VersementFactory extends Factory
{
    protected $model = Versement::class;

    public function definition(): array
    {
        $definition = [
            'nombre_pain_matin' => $this->faker->randomNumber(),
            'nombre_pain_soir' => $this->faker->randomNumber(),
            'nombre_retour' => $this->faker->randomNumber(),
            'montant_verse' => $this->faker->numberBetween(10000, 900000),
            'date_versement' => Carbon::now(),
            'caisse_id' => Caisse::factory(),
            'compte_data'=>(new CompteLivreur([
                'solde_pain'=> $this->faker->randomNumber(),
                'solde_reliquat'=> $this->faker->randomNumber(),
                "dette" => $this->faker->randomNumber(),
                "prix_pain" => $this->faker->randomNumber(),
            ]))->toArray(),
        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory()::mockActiveBoulangerie()->id;
        }
        return $definition;
    }
}
