<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Intrant;
use App\Models\StockIntrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntrantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_intrants()
    {
          Intrant::factory()->count(3)->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->getJson('/api/intrants');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        /*
        Response content will be like
        [
    {
        "id": 1,
        "nom": "Levure",
        "stock": 0
    },
    {
        "id": 2,
        "nom": "Améliorant",
        "stock": 0
    },
    {
        "id": 3,
        "nom": "Améliorant 2",
        "stock": 0
    }
]
         */
        // test json structure
        $response->assertJsonStructure([
            '*' => ['id', 'nom', 'stock']
        ]);

    }

    public function test_store_creates_new_intrant()
    {
        $data = [
            'nom' => 'Farine'
        ];

        $response = $this->postJson('/api/intrants', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('intrants', $data);
        $intrant = Intrant::where('nom', 'Farine')->first();
        $this->assertNotNull($intrant);
        $this->assertDatabaseHas('stock_intrants', [
            'quantite' => 0,
            'nom' => 'Stock de Farine',
            'code_bar' => now()->timestamp,
            'prix_achat' => 0,
            'boulangerie_id' => $intrant->boulangerie_id
        ]);
    }

    public function test_show_returns_specific_intrant()
    {
        $intrant = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->getJson('/api/intrants/' . $intrant->id);

        $response->assertStatus(200);
        $response->assertJson($intrant->toArray());
    }

    public function test_update_modifies_existing_intrant()
    {
        $intrant = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $data = ['nom' => 'Levure'];

        $response = $this->putJson('/api/intrants/' . $intrant->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('intrants', array_merge(['id' => $intrant->id], $data));
    }

    public function test_destroy_deletes_intrant()
    {
        $intrant = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $response = $this->deleteJson('/api/intrants/' . $intrant->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('intrants', ['id' => $intrant->id]);
    }
}
