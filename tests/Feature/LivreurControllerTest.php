<?php

namespace Tests\Feature;

use App\Models\Boulangerie;
use App\Models\Livreur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivreurControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $boulangerie;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->boulangerie = Boulangerie::factory()->create();
    }

    public function test_index_returns_all_livreurs()
    {
        Livreur::factory()->count(3)->create(['boulangerie_id' => $this->boulangerie->id, 'is_active' => true]);

        $response = $this->getJson('/api/livreurs');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_store_creates_new_livreur()
    {
        $data = [
            'prenom' => 'John',
            'nom' => 'Doe',
            'telephone' => '123456789'
        ];

        $response = $this->postJson('/api/livreurs', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('livreurs', $data);
    }

    public function test_update_modifies_existing_livreur()
    {
        $livreur = Livreur::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $data = [
            'prenom' => 'Jane',
            'nom' => 'Smith',
            'telephone' => '987654321'
        ];

        $response = $this->putJson('/api/livreurs/' . $livreur->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('livreurs', $data);
    }

    public function test_destroy_deletes_livreur()
    {
        $livreur = Livreur::factory()->create(['boulangerie_id' => $this->boulangerie->id]);

        $response = $this->deleteJson('/api/livreurs/' . $livreur->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('livreurs', ['id' => $livreur->id]);
    }

    public function test_disable_livreur()
    {
        $livreur = Livreur::factory()->create(['boulangerie_id' => $this->boulangerie->id, 'is_active' => true]);

        $response = $this->putJson('/api/livreurs/' . $livreur->id . '/activate/1');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Livreur disabled successfully']);
        $this->assertDatabaseHas('livreurs', ['id' => $livreur->id, 'is_active' =>true]);
        $response2 = $this->putJson('/api/livreurs/' . $livreur->id . '/activate/0');
        $response2->assertStatus(200);
        $response2->assertJson(['message' => 'Livreur disabled successfully']);
        $this->assertDatabaseHas('livreurs', ['id' => $livreur->id, 'is_active' => false]);
    }
}
