<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\Depense;
use App\Models\TypeDepense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DepenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_depenses()
    {


        $response = $this->getJson('/api/depenses');

        $response->assertStatus(200);
    }

    public function test_store_creates_new_depense()
    {

        $typeDepense = TypeDepense::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);
        $data = [
            'type_depense_id' => $typeDepense->id,
            'montant' => 10000,
            'commentaire' => 'Test Commentaire'
        ];

        $response = $this->postJson('/api/depenses', $data);

        $response->assertStatus(201);
        $reponseContent = $response->json();
       $solde = Caisse::find($reponseContent['caisse_id'])->solde;
        $this->assertSame($solde, -10000);
        $this->assertDatabaseHas('depenses', $data);
        $response = $this->getJson('/api/depenses');


        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_show_returns_depense_by_id()
    {
        $depense = Depense::factory()->create(["boulangerie_id"=>$this->boulangerie->id]);

        $this->actingAs(User::factory()->create());
        $response = $this->getJson('/api/depenses/' . $depense->id);

        $response->assertStatus(200);
        $response->assertJson($depense->toArray());
    }

    public function test_update_depense_increase_decreases_balance()
    {
        // When depense increases, balance should DECREASE (more money spent)
        $typeDepense = TypeDepense::factory()->make();
        $typeDepense->boulangerie_id = $this->boulangerie->id;
        $typeDepense->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 40000;
        $caisse->save();

        $depense = new Depense([
            'type_depense_id' => $typeDepense->id,
            'montant' => 10000,
            'caisse_id' => $caisse->id,
            'commentaire' => 'Test expense'
        ]);
        $depense->boulangerie_id = $this->boulangerie->id;
        $depense->save();

        // Reset caisse to a known value
        $caisse->solde = 40000;
        $caisse->save();

        $updatedData = [
            'montant' => 15000,  // Increased by 5000
            'commentaire' => 'Updated Commentaire'
        ];

        $response = $this->putJson('/api/depenses/' . $depense->id, $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('depenses', $updatedData);

        // Balance should DECREASE by 5000 (40000 - 5000 = 35000)
        $caisse->refresh();
        $this->assertSame(35000, $caisse->solde);
    }

    public function test_update_depense_decrease_increases_balance()
    {
        // When depense decreases, balance should INCREASE (less money spent)
        $typeDepense = TypeDepense::factory()->make();
        $typeDepense->boulangerie_id = $this->boulangerie->id;
        $typeDepense->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 40000;
        $caisse->save();

        $depense = new Depense([
            'type_depense_id' => $typeDepense->id,
            'montant' => 15000,
            'caisse_id' => $caisse->id,
            'commentaire' => 'Test expense'
        ]);
        $depense->boulangerie_id = $this->boulangerie->id;
        $depense->save();

        // Reset caisse to a known value
        $caisse->solde = 40000;
        $caisse->save();

        $updatedData = [
            'montant' => 10000,  // Decreased by 5000
            'commentaire' => 'Reduced expense'
        ];

        $response = $this->putJson('/api/depenses/' . $depense->id, $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('depenses', $updatedData);

        // Balance should INCREASE by 5000 (40000 + 5000 = 45000)
        $caisse->refresh();
        $this->assertSame(45000, $caisse->solde);
    }

    public function test_update_depense_same_amount_no_balance_change()
    {
        // When depense stays the same, balance should not change
        $typeDepense = TypeDepense::factory()->make();
        $typeDepense->boulangerie_id = $this->boulangerie->id;
        $typeDepense->save();

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $caisse->solde = 40000;
        $caisse->save();

        $depense = new Depense([
            'type_depense_id' => $typeDepense->id,
            'montant' => 10000,
            'caisse_id' => $caisse->id,
            'commentaire' => 'Test expense'
        ]);
        $depense->boulangerie_id = $this->boulangerie->id;
        $depense->save();

        // Reset caisse to a known value
        $caisse->solde = 40000;
        $caisse->save();

        $updatedData = [
            'montant' => 10000,  // Same amount
            'commentaire' => 'Only comment changed'
        ];

        $response = $this->putJson('/api/depenses/' . $depense->id, $updatedData);

        $response->assertStatus(200);

        // Balance should remain unchanged
        $caisse->refresh();
        $this->assertSame(40000, $caisse->solde);
    }

    public function test_destroy_deletes_depense()
    {
        $depense = Depense::factory()->create(["boulangerie_id"=>$this->boulangerie->id]);

//        $this->actingAs($depense->caisse->boulangerie->user);
        $response = $this->deleteJson('/api/depenses/' . $depense->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
    }

    public function test_returns_depenses_for_a_specific_date()
    {
        // Arrange
        $caisse = $this->caisse;
        $typeDepense = TypeDepense::factory()->make();
        $typeDepense->boulangerie_id = $this->boulangerie->id;
        $typeDepense->nom = 'Achat café';
        $typeDepense->save();

        $date = Carbon::today()->toDateString();
        $depense = new Depense([
            'type_depense_id' => $typeDepense->id,
            'montant' => 5000,
            'commentaire' => 'Achat café',
            'caisse_id' => $caisse->id,
        ]);
        $depense->boulangerie_id = $this->boulangerie->id;
        $depense->created_at = $date;
        $depense->save();

        // Act
        $response = $this->getJson("api/depenses/date/{$date}");

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([[
            'depense',
            'montant',
            'commentaire'
        ]]);
        $response->assertJson([[
            'depense' => 'Achat café',
            'montant' => 5000,
            'commentaire' => 'Achat café'
        ]]);
    }

}
