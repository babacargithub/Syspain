<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->word,
            'prix' => $this->faker->numberBetween(100, 10000),
            'boulangerie_id' => Boulangerie::factory()::mockActiveBoulangerie()->id,
        ];
    }
}
