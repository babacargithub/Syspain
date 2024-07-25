<?php

namespace Database\Factories;

use App\Models\ArticleProdPatisserie;
use App\Models\Article;
use App\Models\ProdPatisserie;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleProdPatisserieFactory extends Factory
{
    protected $model = ArticleProdPatisserie::class;

    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'prod_patisserie_id' => ProdPatisserie::factory(),
            'quantite' => $this->faker->numberBetween(1, 100),
        ];
    }
}
