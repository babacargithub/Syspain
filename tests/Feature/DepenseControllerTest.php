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

        $user = User::factory()->create();
        $this->actingAs($user);
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
        $depense = Depense::factory()->create(["boulangerie_id"=>Boulangerie::requireBoulangerieOfLoggedInUser()->id]);

        $this->actingAs(User::factory()->create());
        $response = $this->getJson('/api/depenses/' . $depense->id);

        $response->assertStatus(200);
        $response->assertJson($depense->toArray());
    }

    public function test_update_modifies_existing_depense()
    {
        $depense = Depense::factory()->create(["montant" => 10000,"boulangerie_id"=>Boulangerie::requireBoulangerieOfLoggedInUser()->id]);
        $caisse = Caisse::find($depense->caisse_id);
        $caisse->solde = 40000;
        $caisse->save();
        $updatedData = [
            'montant' => 15000,
            'commentaire' => 'Updated Commentaire'
        ];

        $response = $this->putJson('/api/depenses/' . $depense->id, $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('depenses', $updatedData);
        // check caisse solde after update
        $caisse->refresh();
        $this->assertSame($caisse->solde, 45000);

    }

    public function test_destroy_deletes_depense()
    {
        $depense = Depense::factory()->create(["boulangerie_id"=>Boulangerie::requireBoulangerieOfLoggedInUser()->id]);

//        $this->actingAs($depense->caisse->boulangerie->user);
        $response = $this->deleteJson('/api/depenses/' . $depense->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
    }

    public function test_returns_depenses_for_a_specific_date()
    {
        // Arrange
        $boulangerie = Boulangerie::factory()->create();
        $caisse = Caisse::factory()->create(['boulangerie_id' => $boulangerie->id]);
        $typeDepense = TypeDepense::factory()->create(['nom' => 'Achat café']);
        $date = Carbon::today()->toDateString();
        $depense = Depense::factory()->create([
            'boulangerie_id' => $boulangerie->id,
            'type_depense_id' => $typeDepense->id,
            'montant' => 5000,
            'commentaire' => 'Achat café',
            'caisse_id' => $caisse->id,
            'created_at' => $date
        ]);

        // Act
        $response = $this->getJson("api/depenses/date/{$date}"); // Adjust the URL as needed

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
