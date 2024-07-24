<?php

namespace Database\Factories;

use App\Models\Caisse;
use App\Models\Depense;
use App\Models\TypeDepense;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DepenseFactory extends Factory
{
    protected $model = Depense::class;

    public function definition(): array
    {
        $definition= [
            'type_depense_id' => TypeDepense::factory(),
            'montant' => $this->faker->numberBetween(1000, 1000000),
            'commentaire' => $this->faker->sentence(10),
        ];
        if (app()->environment('testing')) {
            $definition["caisse_id"] =  Caisse::requireCaisseOfLoggedInUser();
        }
        return $definition;
    }
}
