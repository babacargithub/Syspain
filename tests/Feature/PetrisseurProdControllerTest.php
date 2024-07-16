<?php

namespace Tests\Feature;

use App\Models\ProductionPetrisseur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetrisseurProdControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_productions()
    {
        ProductionPetrisseur::factory()->count(3)->create();

        $response = $this->getJson('/api/petrisseurs');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_production_du_jour_returns_productions_for_given_date()
    {
        $date = '2024-07-05';
        $prod = ProductionPetrisseur::factory()->make(['date_production' => $date]);
        $prod->save();

        $response = $this->getJson('/api/production_petrisseur/' . $date);

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertGreaterThanOrEqual(1, count($responseData));    }

    public function test_store_creates_new_production()
    {

        $data = [
            'date_production' => '2024-07-05',
            'nombre_chariot' => 5,
            'nombre_plat' => 10,
            'nombre_pain' => 100,
        ];


        $response = $this->postJson('/api/petrisseurs', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas(/** @lang sql */ 'production_petrisseurs', $data);
    }

    public function test_show_returns_production_by_id()
    {
        $production = ProductionPetrisseur::factory()->make();
        $production->save();

        $response = $this->getJson('/api/petrisseurs/' . $production->id);

        $response->assertStatus(200);
        $response->assertJson($production->toArray());
    }

    public function test_update_modifies_existing_production()
    {
        $production = ProductionPetrisseur::first();
        if (!$production) {
            $production = ProductionPetrisseur::factory()->make();
            $production->save();
        }
        $data = [
            'date_production' => fake()->date(),
            'nombre_chariot' => 7,
            'nombre_plat' => 12,
            'nombre_pain' => 150,
        ];

        $response = $this->putJson('/api/petrisseurs/' . $production->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('production_petrisseurs', $data);
    }

    public function test_destroy_deletes_production()
    {
            $production = ProductionPetrisseur::first();
        if (!$production) {
            $production = ProductionPetrisseur::factory()->make();
            $production->save();
        }

        $response = $this->deleteJson('/api/petrisseurs/' . $production->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('production_petrisseurs', ['id' => $production->id]);
    }
}
