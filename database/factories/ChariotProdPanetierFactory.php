<?php

namespace Database\Factories;

use App\Models\Chariot;
use App\Models\ChariotProdPanetier;
use App\Models\ProductionPanetier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ChariotProdPanetierFactory extends Factory
{
    protected $model = ChariotProdPanetier::class;

    public function definition(): array
    {
        return [
            'production_panetier_id' => ProductionPanetier::factory(),
            'chariot_id' => Chariot::factory(),
            'nombre' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
