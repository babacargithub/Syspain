<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
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
    private ?User $user;
    protected ?Boulangerie $boulangerie;
    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);


        // Create a company and a boulangerie
        $company = Company::factory()->create();
        $boulangerie = Boulangerie::factory()->create(['company_id' => $company->id]);
        $this->boulangerie = $boulangerie;

        // Associate the authenticated user with the boulangerie
//        Auth::shouldReceive('user')->andReturn($user);
//        $user->boulangeries()->attach($boulangerie);
    }

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

    public function test_store_creates_new_production()
    {
        $intrantFarine = Intrant::factory()->for($this->boulangerie)->create(['nom' => 'farine']);
        $intrantFarine->stock->quantite = 100;
        $intrantFarine->stock->save();
        $data = [
            'date_production' => '2024-07-12',
            'nombre_chariot' => 5,
            'nombre_sac'=> 10,
            'nombre_plat' => 10,
            'nombre_pain' => 100,
        ];

        $response = $this->postJson('/api/petrisseurs', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('production_petrisseurs', $data);

        $intrantFarine->refresh();
        $this->assertEquals(90, $intrantFarine->stock->quantite);
    }

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
