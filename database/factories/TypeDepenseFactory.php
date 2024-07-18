<?php

namespace Database\Factories;

use App\Models\TypeDepense;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeDepenseFactory extends Factory
{
    protected $model = TypeDepense::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->name(),
        ];
    }
}
