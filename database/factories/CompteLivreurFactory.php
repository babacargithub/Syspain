<?php

namespace Database\Factories;

use App\Models\CompteLivreur;
use App\Models\Livreur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CompteLivreurFactory extends Factory
{
    protected $model = CompteLivreur::class;

    public function definition(): array
    {
        return [
            'solde_pain' => 0,
            'dette' =>0,
            'solde_reliquat' => 0,
        ];
    }
}
