<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Chariot;
use App\Models\ChariotProdPanetier;
use App\Models\Client;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdPanetierControllerTest extends TestCase
{
    use RefreshDatabase;
    private ?Boulangerie $boulangerie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->boulangerie = Boulangerie::factory()->create();
    }

    public function test_index_returns_all_productions()
    {
        $date1 = today()->toDateString();
        $date2 = today()->addDays(3)->toDateString();
        $date3 = today()->subDays(3)->toDateString();

        ProductionPanetier::factory()->create([
            'date_production' => $date1,
            'periode' => 'matin',
            'boulangerie_id' => $this->boulangerie->id
        ]);
        ProductionPanetier::factory()->create([
            'date_production' => $date2,
            'periode' => 'soir',
            'boulangerie_id' => $this->boulangerie->id
        ]);
        ProductionPanetier::factory()->create([
            'date_production' => $date3,
            'periode' => 'matin',
            'boulangerie_id' => $this->boulangerie->id
        ]);

        $response = $this->getJson('/api/panetiers');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_production_du_jour_returns_productions_for_given_date()
    {
        $date = '2024-07-05';
        $prod = ProductionPanetier::factory()->make(['date_production' => $date]);
        $prod->save();

        $response = $this->getJson('/api/panetiers/date/' . $date);

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertGreaterThanOrEqual(1, count($responseData));
        $response->assertJsonStructure([
            '*' => [
                'id',
                'boulangerie_id',
                'date_production',
                'nombre_pain',
                'nombre_plat',
                'nombre_sac',
                'ration',
                'donation',
                'casse',
                'mange',
                'prix_pain_client',
                'periode',
                'prix_pain_livreur',
                'created_at',
                'updated_at',
                'nombre_pain_entregistre',
                'total_pain_distribue',
                'total_pain_petrisseur_produit',
                'distrib_panetiers',
            ]
        ]);
    }

    public function test_store_creates_new_production()
    {
        $chariot1 = Chariot::factory()->for($this->boulangerie)->create()->id;
        $chariot2 = Chariot::factory()->for($this->boulangerie)->create()->id;
        $data = [
            'date_production' => '2024-07-05',
            'nombre_plat' => 10,
            'ration' => 5,
            'donation' => 2,
            'casse' => 3,
            'periode' => 'matin',
            'mange' => 1,
            'nombre_sac' => 2, // 'nombre_sac' => 'required|integer
            'nombre_pain' => 100,
        ];

        $chariots = [
            ["chariot_id" => $chariot1, "nombre" => 40],
            ["chariot_id" => $chariot2, "nombre" => 60]
        ];

        $response = $this->postJson('/api/panetiers', array_merge($data, ['chariots' => $chariots]));

        $response->assertStatus(201);

        // Verify main attributes in the production_panetiers table
        $this->assertDatabaseHas('production_panetiers', $data);

        // Retrieve the newly created production_panetier id
        $productionPanetierId = ProductionPanetier::where('date_production', '2024-07-05')

            ->first()->id;

        // Verify chariots association
        foreach ($chariots as $chariot) {
            $this->assertDatabaseHas('chariot_prod_panetiers', [
                'production_panetier_id' => $productionPanetierId,
                'chariot_id' => $chariot['chariot_id'],
                'nombre' => $chariot['nombre']
            ]);
        }
    }


    public function test_show_returns_production_by_id()
    {
        $production = ProductionPanetier::factory()->make();
        $production->save();

        $response = $this->getJson('/api/panetiers/' . $production->id);

        $response->assertStatus(200);
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
                    'chariots'=>[
                        '*' => [
                            'nom',
                            'nombre'
                        ]
                    ],
                    'mange',
                ],
                'livreurs',
                'clients',
                'abonnements',
                'boutiques'

        ]);

    }

    public function test_update_modifies_existing_production()
    {
        $production = ProductionPanetier::first();
        if (!$production) {
            $production = ProductionPanetier::factory()->make();
            $production->save();
        }
        $data = [
            'date_production' => fake()->date(),
            'nombre_plat' => 12,
            'nombre_pain' => 150,
        ];

        $response = $this->putJson('/api/panetiers/' . $production->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('production_panetiers', $data);
    }

    public function test_destroy_deletes_production()
    {
            $production = ProductionPanetier::first();
        if (!$production) {
            $production = ProductionPanetier::factory()->make();
            $production->save();
        }

        $response = $this->deleteJson('/api/panetiers/' . $production->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('production_panetiers', ['id' => $production->id]);
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
