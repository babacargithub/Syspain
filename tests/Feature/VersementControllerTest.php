<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\CompteLivreur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use App\Models\Livreur;
use App\Models\Versement;
use App\Models\Caisse;

class VersementControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_lists_active_livreurs(): void
    {
        // Arrange
        $activeLivreur = Livreur::factory()->create(['is_active' => true]);
        $inactiveLivreur = Livreur::factory()->create(['is_active' => false]);

        // Act
        $response = $this->getJson('api/livreurs'); // Adjust the URL as needed

        // Assert
        $response->assertOk();
        $response->assertJsonFragment(['id' => $activeLivreur->id]);
        $response->assertJsonMissing(['id' => $inactiveLivreur->id]);
    }

    public function test_it_creates_a_versement_for_a_livreur(): void
    {
        // Arrange
        $livreur = Livreur::factory()->create(['is_active' => true]);
        $livreur->prix_pain = 140;
        $livreur->save();
        $livreur->compteLivreur->solde_reliquat = 0;
        $livreur->compteLivreur->dette = 0;
        $livreur->compteLivreur->solde_pain = 358;
        $livreur->compteLivreur->save();
        $caisse = Caisse::factory()->make();
        $caisse->boulangerie()->associate($livreur->boulangerie);
        $caisse->save();
        $data = [
            'montant' => 33600,
            'nombre_retour' => 10,
            'nombre_pain_matin' => $this->faker->numberBetween(1, 10),
            'caisse_id' => $caisse->id,
            'date_versement' => now()->toDateString(),
            'livreur_id' => $livreur->id,
        ];

        // Act
        $response = $this->postJson('api/versements', $data); // Adjust the URL as needed

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('versements', [
            'montant_verse' => $data['montant'],
            'livreur_id' => $livreur->id
        ]);
         //test les soldes pain, dette et reliquat du livreur
        $this->assertDatabaseHas('compte_livreurs', [
            'livreur_id' => $livreur->id,
            'solde_pain' => 0,
            'dette' => 0,
            'solde_reliquat' => 15120
        ]);
    }
    public function test_deletes_versement_and_updates_related_models()
    {
        // Arrange
        $livreur = Livreur::factory()->create();
        $compteLivreur = CompteLivreur::factory()->create(['livreur_id' => $livreur->id]);
        $livreur->prix_pain = 140;
        $livreur->save();
        $livreur->compteLivreur->solde_reliquat = 0;
        $livreur->compteLivreur->dette = 0;
        $livreur->compteLivreur->solde_pain = 358;
        $livreur->compteLivreur->save();
        $caisse = Caisse::factory()->create();
        $data = [
            'montant' => 33600,
            'nombre_retour' => 10,
            'nombre_pain_matin' => $this->faker->numberBetween(1, 10),
            'caisse_id' => $caisse->id,
            'date_versement' => now()->toDateString(),
            'livreur_id' => $livreur->id,
        ];

        // Act
        $response = $this->postJson('api/versements', $data);
        $response->assertCreated();
        $versement = Versement::find($response->json()['id']);

        // Act
        $response = $this->deleteJson(route('versements.destroy', $versement->id));

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('versements', ['id' => $versement->id]);
        $this->assertDatabaseHas('compte_livreurs', [
            'id' => $versement->livreur->compteLivreur->id,
            'solde_pain' => 358,
            'dette' => 0,
            'solde_reliquat' => 15120
            // Assert expected changes in compte_livreur
        ]);
        $this->assertDatabaseHas('caisses', [
            'id' => $caisse->id,
            'solde' => 0
            // Assert expected changes in caisse balance
        ]);
    }
    public function show(Versement $versement): JsonResponse
    {
        return response()->json($versement, 200);
    }

    public function test_returns_versements_for_a_specific_date()
    {
        // Arrange
        $boulangerie = $this->boulangerie;
        $this->actingAsUserWithBoulangerie($boulangerie); // Assume this method sets the logged-in user's boulangerie

        $date = Carbon::today()->toDateString();
        $versementsToday = Versement::factory()->count(3)->create([
            'boulangerie_id' => $boulangerie->id,
            'created_at' => $date,
        ]);

        $versementsOtherDay = Versement::factory()->count(2)->create([
            'boulangerie_id' => $boulangerie->id,
            'created_at' => Carbon::yesterday()->toDateString(),
        ]);

        // Act
        $response = $this->getJson(route('versements.date', ['date' => $date]));

        // Assert
        $response->assertOk();
        $response->assertJsonCount(3); // Expecting 3 versements for today
        $response->assertJsonFragment(['id' => $versementsToday->first()->id]);
        $response->assertJsonMissing(['id' => $versementsOtherDay->first()->id]);
    }

    protected function actingAsUserWithBoulangerie(Boulangerie $boulangerie)
    {
        return User::factory()->create();
    }

}