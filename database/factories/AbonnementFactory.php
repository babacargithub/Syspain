<?php

namespace Database\Factories;

use App\Models\Abonnement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AbonnementFactory extends Factory
{
    protected $model = Abonnement::class;

    public function definition(): array
    {
        return [
            'date_debut' => Carbon::now()->toDateString(),
            'date_fin' => Carbon::now()->addMonth()->toDateString()
        ];
    }
}
