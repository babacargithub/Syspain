<?php

namespace Database\Factories;

use App\Models\Boulangerie;
use App\Models\TypeDepense;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeDepenseFactory extends Factory
{
    protected $model = TypeDepense::class;

    public function definition(): array
    {
        $definition = [
            'nom' => $this->faker->name(),
        ];
        if (app()->environment('testing')) {
            $definition["boulangerie_id"] =  Boulangerie::factory()::mockActiveBoulangerie()->id;
        }
        return $definition;
    }
}
