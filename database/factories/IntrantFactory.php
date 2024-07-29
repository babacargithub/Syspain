<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Intrant;
use App\Models\StockIntrant;
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

        });
    }
}
