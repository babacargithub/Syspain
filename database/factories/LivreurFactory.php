<?php

namespace Database\Factories;

use App\Models\Livreur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LivreurFactory extends Factory
{
    protected $model = Livreur::class;

    public function definition(): array
    {
        return [
            'prenom' => $this->faker->firstName(),
            'nom' => $this->faker->lastName(),
            'telephone' => $this->faker->phoneNumber(),
        ];
    }
}
