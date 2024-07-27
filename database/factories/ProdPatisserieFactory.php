<?php

namespace Database\Factories;

use App\Models\ProdPatisserie;
use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdPatisserieFactory extends Factory
{
    protected $model = ProdPatisserie::class;

    public function definition(): array
    {
        return [
            'date_production' => today()->toDateString(),
            'periode' => $this->faker->randomElement(['matin', 'soir']),
//            'boulangerie_id' => Boulangerie::factory()::mockActiveBoulangerie(),
        ];
    }
}
