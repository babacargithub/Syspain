<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Chariot;
use App\Models\Company;
use App\Models\Intrant;
use App\Models\ProductionPetrisseur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PetrisseurControllerTest extends TestCase
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
        $date = '2024-07-12';
        ProductionPetrisseur::factory()->create(['date_production' => $date]);
        ProductionPetrisseur::factory()->create(['date_production' => '2024-07-11']); // Another date

        $response = $this->getJson('/api/production_petrisseur/' . $date);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

//    public function test_store_creates_new_production()
//    {
//        // Create Chariot records
//        $dee2 = Chariot::create([
//            'nom' => fake()->unique()->word(),
//            'nombre_pain' => 2349,
//            'boulangerie_id' => $this->boulangerie->id
//        ]);
//
//        $dee = Chariot::create([
//            'nom' => fake()->unique()->word(),
//            'nombre_pain' => fake()->numberBetween(10, 99),
//            'boulangerie_id' => $this->boulangerie->id
//        ]);
//
//        // Check created Chariot records
//        dump($dee, $dee2);
//        dd(Chariot::all());
//
//        $data = [
//            'date_production' => '2024-07-12',
//            'nombre_chariot' => 5,
//            'nombre_sac' => 10,
//            'nombre_plat' => 10,
//            'nombre_pain' => 100,
//            'chariots' => Chariot::all()->map(function ($chariot) {
//                return [
//                    'chariot_id' => $chariot->id,
//                    'nombre' => fake()->numberBetween(10, 20),
//                ];
//            })->toArray(),
//        ];
//
//        $response = $this->postJson('/api/petrisseurs', $data);
//
//        $response->assertStatus(201);
//
//        $dataWithoutChariots = $data;
//        unset($dataWithoutChariots['chariots']);
//        $this->assertDatabaseHas('production_petrisseurs', $dataWithoutChariots);
//
//        // Ensure the chariot_prod_petrisseurs table is populated correctly
//        $this->assertDatabaseCount('chariot_prod_petrisseurs', 2);
//
//        $intrantFarine = Intrant::factory()->for($this->boulangerie)->create(['nom' => 'farine']);
//        $stock = $intrantFarine->stock;
//
//        if ($stock != null) {
//            $intrantFarine->stock->quantite = 100;
//            $intrantFarine->stock->save();
//            $intrantFarine->refresh();
//            $this->assertEquals(90, $intrantFarine->stock->quantite);
//        }
//    }

    public function test_show_returns_production_by_id()
    {
        $production = ProductionPetrisseur::factory()->create();

        $response = $this->getJson('/api/petrisseurs/' . $production->id);

        $response->assertStatus(200);
        $response->assertJson($production->toArray());
    }

    public function test_update_modifies_existing_production()
    {
        $production = ProductionPetrisseur::factory()->create();
        $data = [
            'date_production' => '2024-07-12',
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
        $production = ProductionPetrisseur::factory()->create();

        $response = $this->deleteJson('/api/petrisseurs/' . $production->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('production_petrisseurs', ['id' => $production->id]);
    }
}
