<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\Recette;
use App\Models\TypeRecette;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_recettes()
    {
        $typeRecette = TypeRecette::factory()->make();
        $typeRecette->boulangerie_id = $this->boulangerie->id;
        $typeRecette->save();
        $caisse = Caisse::requireCaisseOfLoggedInUser();

        $recettes = Recette::factory()->count(3)->create(['type_recette_id' => $typeRecette->id,
            "caisse_id" => $caisse->id,
            "boulangerie_id" => $caisse->boulangerie_id]);

        $response = $this->getJson('/api/recettes');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => ['id', 'montant', 'identifier', 'created_at'],
        ]);
    }

    public function test_store_creates_new_recette()
    {
        $typeRecette = TypeRecette::factory()->create();
        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 10000;
        $caisse->save();

        $data = [
            'montant' => 2500,
            'type_recette_id' => $typeRecette->id,
            "caisse_id" => $caisse->id,
        ];

        $response = $this->postJson('/api/recettes', $data);

        $response->assertStatus(201);
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('recettes', $data);
        $caisse->refresh();
        $this->assertEquals(12500, $caisse->solde);
    }

    public function test_show_returns_recette()
    {
        $typeRecette = TypeRecette::factory()->create();
        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $recette = Recette::factory()->create(['type_recette_id' => $typeRecette->id,
            "caisse_id" => $caisse->id,
            "boulangerie_id" => $caisse->boulangerie_id]);

        $response = $this->getJson('/api/recettes/' . $recette->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $recette->id,
            'montant' => $recette->montant,
            'commentaire' => $recette->commentaire,
            'created_at' => $recette->created_at->toJson(),
            'identifier' => $recette->identifier(),

        ]);
    }

    public function test_destroy_deletes_recette()
    {
        $typeRecette = TypeRecette::factory()->create();
        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 10000;
        $caisse->save();
        $recette = Recette::factory()->create(['type_recette_id' => $typeRecette->id,
            "caisse_id" => $caisse->id,
            "boulangerie_id" => $caisse->boulangerie_id]);
        $recette->montant = 2500;
        $recette->save();

        $response = $this->deleteJson('/api/recettes/' . $recette->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('recettes', ['id' => $recette->id]);

        $caisse->refresh();
        $this->assertEquals(7500, $caisse->solde);

    }
    public function test_recettes_date_returns_data()
    {
        $typeRecette = TypeRecette::factory()->create();
        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $recettes = Recette::factory()->count(10)->create([
            'type_recette_id' => $typeRecette->id,
            "montant" => 1000,
            "caisse_id" => $caisse->id,
            "boulangerie_id" => $caisse->boulangerie_id]);

        $response = $this->getJson('/api/recettes/date/' . today()->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertJsonCount(10);
        $response->assertJsonStructure([
            '*' => ['id', 'montant', 'identifier', 'created_at']]);

        $response = $this->getJson('/api/recettes/date/' . today()->addDay()->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_update_recette_increase_increases_balance()
    {
        // When recette increases, balance should INCREASE
        $typeRecette = TypeRecette::factory()->make();
        $typeRecette->boulangerie_id = $this->boulangerie->id;
        $typeRecette->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 10000;
        $caisse->save();

        $recette = new Recette([
            'type_recette_id' => $typeRecette->id,
            'montant' => 5000,
            'caisse_id' => $caisse->id,
            'boulangerie_id' => $this->boulangerie->id
        ]);
        $recette->save();

        // Reset caisse to a known value after creation
        $caisse->solde = 10000;
        $caisse->save();

        $updatedData = [
            'montant' => 8000,  // Increased by 3000
        ];

        $response = $this->putJson('/api/recettes/' . $recette->id, $updatedData);

        $response->assertStatus(200);

        // Balance should INCREASE by 3000 (10000 + 3000 = 13000)
        $caisse->refresh();
        $this->assertEquals(13000, $caisse->solde);
    }

    public function test_update_recette_decrease_decreases_balance()
    {
        // When recette decreases, balance should DECREASE
        $typeRecette = TypeRecette::factory()->make();
        $typeRecette->boulangerie_id = $this->boulangerie->id;
        $typeRecette->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 10000;
        $caisse->save();

        $recette = new Recette([
            'type_recette_id' => $typeRecette->id,
            'montant' => 8000,
            'caisse_id' => $caisse->id,
            'boulangerie_id' => $this->boulangerie->id
        ]);
        $recette->save();

        // Reset caisse to a known value after creation
        $caisse->solde = 10000;
        $caisse->save();

        $updatedData = [
            'montant' => 5000,  // Decreased by 3000
        ];

        $response = $this->putJson('/api/recettes/' . $recette->id, $updatedData);

        $response->assertStatus(200);

        // Balance should DECREASE by 3000 (10000 - 3000 = 7000)
        $caisse->refresh();
        $this->assertEquals(7000, $caisse->solde);
    }

    public function test_update_recette_same_amount_no_balance_change()
    {
        // When recette stays the same, balance should not change
        $typeRecette = TypeRecette::factory()->make();
        $typeRecette->boulangerie_id = $this->boulangerie->id;
        $typeRecette->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 10000;
        $caisse->save();

        $recette = new Recette([
            'type_recette_id' => $typeRecette->id,
            'montant' => 5000,
            'caisse_id' => $caisse->id,
            'boulangerie_id' => $this->boulangerie->id
        ]);
        $recette->save();

        // Reset caisse to a known value after creation
        $caisse->solde = 10000;
        $caisse->save();

        $updatedData = [
            'montant' => 5000,  // Same amount
            'commentaire' => 'Only comment changed'
        ];

        $response = $this->putJson('/api/recettes/' . $recette->id, $updatedData);

        $response->assertStatus(200);

        // Balance should remain unchanged
        $caisse->refresh();
        $this->assertEquals(10000, $caisse->solde);
    }
}
