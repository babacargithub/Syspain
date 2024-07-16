<?php

namespace Database\Factories;

use App\Models\Boutique;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BoutiqueFactory extends Factory
{
    protected $model = Boutique::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->company(),
            'solde_pain' => 0,
            'adresse' => $this->faker->address(),

        ];
    }
}
