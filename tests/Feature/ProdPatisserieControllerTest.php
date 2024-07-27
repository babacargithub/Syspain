<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleProdPatisserie;
use App\Models\Boulangerie;
use App\Models\ProdPatisserie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProdPatisserieControllerTest extends TestCase
{
    use RefreshDatabase;


    public function test_index_returns_all_prod_patisseries()
    {
        ProdPatisserie::factory()->count(2)->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->getJson('/api/production_patisseries');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_store_creates_new_prod_patisserie()
    {
        $data = [
            'date_production' => '2024-07-22',
            'periode' => 'matin',
        ];

        $response = $this->postJson('/api/production_patisseries', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('prod_patisseries', $data);

        // We should get a 422 error
        $response422 = $this->postJson('/api/production_patisseries', $data);
        $response422->assertStatus(422);

    }

    public function test_show_returns_prod_patisserie_with_articles()
    {
        $prodPatisserie = ProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        ArticleProdPatisserie::factory()->count(2)->create(['prod_patisserie_id' => $prodPatisserie->id]);

        $response = $this->getJson('/api/prod_patisseries/' . $prodPatisserie->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'date_production', 'periode', 'articles']);
    }

    public function test_update_modifies_existing_prod_patisserie()
    {
        $prodPatisserie = ProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $data = [
            'date_production' => '2024-07-23',
        ];

        $response = $this->putJson('/api/prod_patisseries/' . $prodPatisserie->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('prod_patisseries', $data);
    }

    public function test_destroy_deletes_prod_patisserie()
    {
        $prodPatisserie = ProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->deleteJson('/api/prod_patisseries/' . $prodPatisserie->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('prod_patisseries', ['id' => $prodPatisserie->id]);
    }

    public function test_store_articles_creates_new_articles_for_prod_patisserie()
    {
        $prodPatisserie = ProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $article = Article::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $data = [
            'articles' => [
                ['article_id' => $article->id, 'quantite' => 10],
            ],
        ];

        $response = $this->postJson('/api/prod_patisseries/' . $prodPatisserie->id . '/articles', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('article_prod_patisseries', ['prod_patisserie_id' => $prodPatisserie->id, 'article_id' => $article->id, 'quantite' => 10]);
    }

    public function test_get_articles_returns_articles_of_prod_patisserie()
    {
        $prodPatisserie = ProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $articleProdPatisserie = ArticleProdPatisserie::factory()->create(['prod_patisserie_id' => $prodPatisserie->id]);

        $response = $this->getJson('/api/prod_patisseries/' . $prodPatisserie->id . '/articles');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $articleProdPatisserie->id, 'article_id' => $articleProdPatisserie->article_id]);
    }

    public function test_delete_article_removes_article_from_prod_patisserie()
    {
        $articleProdPatisserie = ArticleProdPatisserie::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->deleteJson('/api/article_prod_patisseries/' . $articleProdPatisserie->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('article_prod_patisseries', ['id' => $articleProdPatisserie->id]);
    }
}
