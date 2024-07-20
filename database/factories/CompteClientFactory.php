<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\CompteClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CompteClientFactory extends Factory
{
    protected $model = CompteClient::class;

    public function definition(): array
    {
        return [
            'solde_pain' => 0,
            'dette' => 0,
            'solde_reliquat' => 0,
        ];
    }
}
