<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Intrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase;


    public function test_entreeStock_creates_new_stock_entries()
    {
        $intrant1 = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $intrant2 = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $data = [
            'intrants' => [
                [
                    'intrant_id' => $intrant1->id,
                    'quantite' => 10,
                    'prix_achat' => 5.5
                ],
                [
                    'intrant_id' => $intrant2->id,
                    'quantite' => 20,
                    'prix_achat' => 2.5
                ]
            ]
        ];

        $response = $this->postJson('/api/stocks/entree', $data);

        $response->assertStatus(200); // Assuming the controller returns a 200 status code on success

        $this->assertDatabaseHas('stock_intrants', [
            'intrant_id' => $intrant1->id,
            'quantite' => 10,
            'prix_achat' => 5.5,
        ]);

        $this->assertDatabaseHas('stock_intrants', [
            'intrant_id' => $intrant2->id,
            'quantite' => 20,
            'prix_achat' => 2.5,
        ]);
    }

    public function test_sortieStock_updates_stock()
    {
        $intrant = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $stock = $intrant->stock;
         $stock->update([
            'quantite' => 20,
            'prix_achat' => 2.5,
        ]);
         $stock->refresh();

        $data = [
            'quantite' => 10,
        ];

        $response = $this->postJson('/api/stocks/sortie/' . $intrant->id, $data);

        $response->assertStatus(200); // Assuming the controller returns a 200 status code on success

        $this->assertDatabaseHas('stock_intrants', [
            'id' => $stock->id,
            'quantite' => 10,
        ]);
    }

    public function test_sortieStock_fails_when_quantity_is_insufficient()
    {
        $intrant = Intrant::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $stock = $intrant->stock;
        $stock->quantite = 5;
        $stock->save();

        $data = [
            'quantite' => 10,
        ];

        $response = $this->postJson('/api/stocks/sortie/' . $intrant->id, $data);

        $response->assertStatus(422); // Assuming the controller returns a 422 status code for validation errors

        $this->assertDatabaseHas('stock_intrants', [
            'id' => $stock->id,
            'quantite' => 5,
        ]);
    }
}
