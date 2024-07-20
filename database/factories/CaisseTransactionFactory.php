<?php

namespace Database\Factories;

use App\Models\CaisseTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaisseTransaction>
 */
class CaisseTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'montant' => $this->faker->randomNumber(9),
            'commentaire' => $this->faker->sentence(),
            'solde_avant' => $this->faker->randomNumber(9),
            'solde_apres' => $this->faker->randomNumber(9),
            'type' => $this->faker->randomElement(['cashin', 'cashout']),
            'metadata' => $this->faker->hslColorAsArray(),
            'created_at' => $this->faker->dateTimeBetween(startDate: '-30 days',     endDate: 'now'),

        ];
    }
}
