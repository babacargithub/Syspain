<?php

namespace Database\Factories;

use App\Models\DistribPanetier;
use App\Models\Livreur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termwind\Components\Li;

class DistribPanetierFactory extends Factory
{
    protected $model = DistribPanetier::class;

    public function definition(): array
    {
        return [
            "livreur_id" => Livreur::first()? Livreur::first()->id : Livreur::factory()->create()->id,
        ];
    }
}
