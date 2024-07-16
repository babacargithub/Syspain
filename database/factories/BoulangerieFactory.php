<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Boulangerie>
 */
class BoulangerieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "nom" => $this->faker->company(),
            "created_at" => now(),
            "company_id" => Company::first()?? Company::factory()->create()->id,
            //
        ];
    }
}
