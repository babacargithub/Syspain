<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_clients()
    {
        Client::factory()->for($this->boulangerie)->count(3)->create();

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_store_creates_new_client()
    {
        $data = [
            'nom' => 'John',
            'prenom' => 'Doe',
            'telephone' => '123456789',

        ];

        $response = $this->postJson('/api/clients', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('clients', $data);
    }

    public function test_show_returns_client_by_id()
    {
        $client = Client::factory()->for($this->boulangerie)->create();

        $response = $this->getJson('/api/clients/' . $client->id);

        $response->assertStatus(200);
        $response->assertJson($client->toArray());
    }

    public function test_update_modifies_existing_client()
    {
        $client = Client::factory()->for($this->boulangerie)->create();
        $data = [
            'nom' => 'Jane',
            'prenom' => 'Doe',
            'telephone' => '987654321',
        ];

        $response = $this->putJson('/api/clients/' . $client->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('clients', $data);
    }

    public function test_destroy_deletes_client()
    {
        $client = Client::factory()->for($this->boulangerie)->create();

        $response = $this->deleteJson('/api/clients/' . $client->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_toggle_changes_active_status()
    {
        $client = Client::factory()->for($this->boulangerie)->create(['is_active' => true]);

        $response = $this->putJson('/api/clients/' . $client->id . '/toggle');

        $response->assertStatus(200);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'is_active' => false]);
    }
}
