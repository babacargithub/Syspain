<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Chariot;
use App\Models\Client;
use App\Models\DistribPanetier;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DistribPanetierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_new_distribPanetier()
    {
        // create a productionPanetier
        $chariot1 = Chariot::factory()->for($this->boulangerie)->create();
        $chariot2 = Chariot::factory()->for($this->boulangerie)->create();
        $response = $this->postJson("api/panetiers",[
            "date_production" => "2024-07-17",
            "nombre_pain" => 10000,
            "nombre_plat" => 3,
            "nombre_sac" => 20,
            "ration" => 100,
            "donation" => 20,
            "casse" => 12,
            "mange" => 12,
            "periode" => "matin",
            "chariots" => [
                ['chariot_id'=>$chariot1->id,"nombre"=>20],
                ['chariot_id'=>$chariot2->id,"nombre"=>30]
            ]
        ]);

        $response->assertStatus(201);

        $productionPanetier = ProductionPanetier::find($response->json()['id']);
        $this->assertNotNull($productionPanetier);




        $secondResponse =   $this->postJson("api/panetiers",[
            "date_production" => "2024-07-17",
            "nombre_pain" => 10000,
            "nombre_plat" => 3,
            "nombre_sac" => 20,
            "ration" => 100,
            "donation" => 20,
            "casse" => 12,
            "mange" => 12,
            "periode" => "matin",
            "chariots" => [
                ['chariot_id'=>$chariot1->id,"nombre"=>20],
                ['chariot_id'=>$chariot2->id,"nombre"=>30]
            ]
        ]);
        $secondResponse->assertStatus(422);

        $livreurCreationResponse = $this->postJson("api/livreurs", [
            "nom" => fake()->firstName(),
            "prenom" => fake()->lastName(),
            "telephone" => 773300853]);
        $livreurCreationResponse->assertStatus(201);
        $livreurCreationResponse->assertStatus(201);

        // Decode the JSON response to get the data
        $livreurData = $livreurCreationResponse->json();
        $livreur = Livreur::find($livreurData['id']);
        $this->assertNotNull($livreur);
        $this->assertEquals($livreurData['nom'], $livreur->nom);
        $this->assertEquals($livreurData['prenom'], $livreur->prenom);
        $this->assertEquals($livreurData['telephone'], $livreur->telephone);

        $secondLivreurCreationResponse = $this->postJson("api/livreurs", [
            "nom" => fake()->firstName(),
            "prenom" => fake()->lastName(),
            "telephone" => 773300853]);
        $secondLivreurCreationResponse->assertStatus(422);
        $secondLivreurCreationResponse->assertJson(['message' => 'Le numéro de téléphone est déjà utilisé']);




        $data = [
            'nombre_pain' => 100,
            'livreur_id' => $livreur->id,
            'paye' => false];
        $response = $this->postJson("/api/distribution_panetiers/{$productionPanetier->id}", $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('distrib_panetiers', ['nombre_pain' => 100]);

        // We check now that compte livreur has the correct values
        $compteLivreur = $livreur->compteLivreur;
        $this->assertNotNull($compteLivreur);
        $this->assertEquals(100, $compteLivreur->solde_pain);
        $this->assertEquals(15000, $compteLivreur->dette);

    }

    public function test_store_fails_when_nombre_pain_exceeds_production()
    {
        $productionPanetier = ProductionPanetier::factory()->create(['nombre_pain' => 1000]);
        $data = [
            'nombre_pain' => 1100030000,
            'livreur_id' => null,
            'client_id' => null,
            'abonnement_id' => null,
            'boutique_id' => null,
            'paye' => false,
            'production_panetier_id' => $productionPanetier->id,
        ];

        $response = $this->postJson("/api/distribution_panetiers/{$productionPanetier->id}", $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Le nombre de pain distribué ne peut pas être supérieur au nombre de pain produit']);
    }

    public function test_update_modifies_existing_distribPanetier()
    {
        $distribPanetier = DistribPanetier::factory()->create();
        $distribPanetier->nombre_pain = 200;
        $distribPanetier->save();
        $data = [
            'nombre_pain' => 300,
        ];
        $this->assertEquals(0, $distribPanetier->livreur->compteLivreur->solde_pain);

        $response = $this->putJson("/api/distribution_panetiers/{$distribPanetier->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('distrib_panetiers', ['id' => $distribPanetier->id, 'nombre_pain' => 300]);

        // We check now that compte livreur has the correct values
        $compteLivreur = $distribPanetier->livreur->compteLivreur;
        $compteLivreur->refresh();
        $this->assertNotNull($compteLivreur);
        $this->assertEquals(100, $compteLivreur->solde_pain);
        $this->assertEquals(15000, $compteLivreur->dette);

    }

    public function test_destroy_deletes_distribPanetier()
    {
        $distribPanetier = DistribPanetier::factory()->create();

        $response = $this->deleteJson("/api/distribution_panetiers/{$distribPanetier->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('distrib_panetiers', ['id' => $distribPanetier->id]);
    }

    public function test_show_returns_distribPanetier()
    {
        $distribPanetier = DistribPanetier::factory()->create();

        $response = $this->getJson("/api/distribution_panetiers/{$distribPanetier->id}");

        $response->assertStatus(200);
        $response->assertJson($distribPanetier->toArray());
    }
    public function test_get_entities_for_distrib_returns_correct_data()
    {
        // Create test data
        $clients = Client::factory()->count(2)->create(['boulangerie_id' => $this->boulangerie->id]);
        $livreurs = Livreur::factory()->count(2)->create(['boulangerie_id' => $this->boulangerie->id, 'is_active' => true]);
        $abonnements = Abonnement::factory()->count(2)->create(['client_id' => $clients->first()->id]);
        $boutiques = Boutique::factory()->count(2)->create(['boulangerie_id' => $this->boulangerie->id]);

        // Call the endpoint
        $response = $this->getJson('/api/distribution_panetiers/destinations');

        // Assertions
        $response->assertStatus(200);

        $responseData = $response->json();

        // Assert clients
        $this->assertCount(2, $responseData['clients']);
        $this->assertEquals($clients->pluck('id')->toArray(), array_column($responseData['clients'], 'id'));
        $this->assertEquals($clients->map->identifier()->toArray(), array_column($responseData['clients'], 'nom'));

        // Assert livreurs
        $this->assertCount(2, $responseData['livreurs']);
        $this->assertEquals($livreurs->pluck('id')->toArray(), array_column($responseData['livreurs'], 'id'));
        $this->assertEquals($livreurs->map->identifier()->toArray(), array_column($responseData['livreurs'], 'nom'));

        // Assert abonnements
        $this->assertCount(2, $responseData['abonnements']);
        $this->assertEquals($abonnements->pluck('id')->toArray(), array_column($responseData['abonnements'], 'id'));
        $this->assertEquals($abonnements->map->identifier()->toArray(), array_column($responseData['abonnements'], 'nom'));

        // Assert boutiques
        $this->assertCount(2, $responseData['boutiques']);
        $this->assertEquals($boutiques->pluck('id')->toArray(), array_column($responseData['boutiques'], 'id'));
        $this->assertEquals($boutiques->pluck('nom')->toArray(), array_column($responseData['boutiques'], 'nom'));
    }

    public function test_get_entities_for_distrib_with_no_entities()
    {
        // Call the endpoint with no entities
        $response = $this->getJson('/api/distribution_panetiers/destinations');

        // Assertions
        $response->assertStatus(200);

        $responseData = $response->json();

        // Assert empty results
        $this->assertEmpty($responseData['clients']);
        $this->assertEmpty($responseData['livreurs']);
        $this->assertEmpty($responseData['abonnements']);
        $this->assertEmpty($responseData['boutiques']);
    }
}
