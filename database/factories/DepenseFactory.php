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
        return [
            'type_depense_id' => TypeDepense::factory(),
            'caisse_id' => Caisse::requireCaisseOfLoggedInUser(),
            'montant' => $this->faker->randomNumber(),
            'commentaire' => $this->faker->word(),
        ];
    }
}
