<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\ChariotProdPanetier;
use App\Models\Client;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use App\Models\ProductionPetrisseur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdPanetierControllerTest extends TestCase
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
    public function test_production_panetier_endpoint_returns_correct_structure()
    {
        // Seed the database or create the necessary records
        $boulangerie = Boulangerie::factory()->create();
        $livreur = Livreur::factory()->make();
        $livreur->boulangerie()->associate($boulangerie);
        $livreur->save();
        $livreur->compteLivreur()->create(['solde_pain' => 0, 'dette' => 0]);

        $client = Client::factory()->make();
        $client->boulangerie()->associate($boulangerie);
        $client->save();
        $client->compteClient()->create(['solde_pain' => 0, 'dette' => 0]);
        $client->abonnement()->create(['nombre_pain' => 13]);
        $boutique = Boutique::factory()->make();
        $boutique->boulangerie()->associate($boulangerie);
        $boutique->save();
        $abonnement = $client->abonnement;

        $productionPanetier = ProductionPanetier::factory()
//            ->has(
//                ChariotProdPanetier::factory()->count(2),
//                'chariots'
//            )
            ->hasDistribPanetiers(2, [
                'nombre_pain' => 13,
                'livreur_id' => Livreur::first()->id
            ])
            ->hasDistribPanetiers(2, [
                'nombre_pain' => 13,
                'client_id' => Client::first()->id
            ])
            ->hasDistribPanetiers(1, [
                'nombre_pain' => 13,
                'abonnement_id' => Abonnement::first()->id
            ])
            ->hasDistribPanetiers(1, [
                'nombre_pain' => 13,
                'boutique_id' => Boutique::first()->id
            ])
            ->create([
                'date_production' => '2024-07-16',
                'nombre_pain' => 10000,
                'nombre_plat' => 50,
                'nombre_sac' => 10,
                'ration' => 5,
                'donation' => 2,
                'casse' => 3,
                'mange' => 1
            ]);

        // Hit the endpoint
        $response = $this->getJson('/api/panetiers/' . $productionPanetier->id);

        // Assert the response status
        $response->assertStatus(200);

        // Assert the JSON structure
        $response->assertJsonStructure([
            'productionPanetier' => [
                'id',
                'date_production',
                'identifier',
                'nombre_pain',
                'nombre_plat',
                'nombre_sac',
                'ration',
                'donation',
                'casse',
                'total_pain_petrisseur_produit',
                'nombre_pain_entregistre',
                'total_pain_distribue',
                'chariots' => [
                    '*' => [
                        'nom',
                        'nombre'
                    ]
                ],
                'mange'
            ],
            'livreurs' => [
                '*' => [
                    'id',
                    'nombre_pain',
                    'livreur'
                ]
            ],
            'clients' => [
                '*' => [
                    'id',
                    'nombre_pain',
                    'client'
                ]
            ],
            'abonnements' => [
                '*' => [
                    'id',
                    'nombre_pain',
                    'abonnement'
                ]
            ],
            'boutiques' => [
                '*' => [
                    'id',
                    'nombre_pain',
                    'boutique'
                ]
            ]
        ]);

        // Assert the JSON values
        $response->assertJson([
            'productionPanetier' => [
                'date_production' => '2024-07-16',
                'nombre_pain' => 10000,
                'nombre_plat' => 50,
                'nombre_sac' => 10,
                'ration' => 5,
                'donation' => 2,
                'casse' => 3,
                'total_pain_petrisseur_produit' => 0,
                'nombre_pain_entregistre' => 500,
                'total_pain_distribue' => 89,
                'mange' => 1
            ],
            'livreurs' => [
                ['nombre_pain' => 13, 'livreur' => $livreur->identifier()],
            ],
            'clients' => [
                ['nombre_pain' => 13, 'client' => $client->identifier()],
            ],
            'abonnements' => [
                ['nombre_pain' => 13, 'abonnement' => $abonnement->identifier()]
            ],
            'boutiques' => [
                ['nombre_pain' => 13, 'boutique' => $boutique->nom]
            ]
        ]);
    }
}
