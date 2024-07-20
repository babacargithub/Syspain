<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\MouveIntrant;
use App\Models\StockIntrant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MouveIntrantFactory extends Factory
{
    protected $model = MouveIntrant::class;

    public function definition(): array
    {
        $definition = [
            'quantite' => $this->faker->randomNumber(),
            'stock_avant' => $this->faker->randomNumber(),
            'stock_apres' => $this->faker->randomNumber(),
            'type' => $this->faker->randomElement(['in', 'out']),
            'metadata' => $this->faker->shuffleArray(['key' => 'value']),

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        if (app()->environment() === 'testing') {
            $definition['boutique_id'] = Boulangerie::factory();
            $definition['stock_intrant_id'] = StockIntrant::factory();
        }
        return  $definition;
    }
}
