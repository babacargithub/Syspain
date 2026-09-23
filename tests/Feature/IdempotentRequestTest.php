<?php

namespace Tests\Feature;

use App\Models\IdempotencyKey;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotentRequestTest extends TestCase
{
    use RefreshDatabase;

    private Livreur $livreur;
    private ProductionPanetier $productionPanetier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->livreur = Livreur::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        $this->productionPanetier = ProductionPanetier::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
    }

    private function distributionPayload(int $nombrePain): array
    {
        return [
            'livreurs' => [['livreur_id' => $this->livreur->id, 'nombre_pain' => $nombrePain, 'bonus' => 0]],
            'clients' => [],
            'abonnements' => [],
            'boutiques' => [],
        ];
    }

    private function postDistribution(array $payload, ?string $idempotencyKey)
    {
        $headers = $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [];
        return $this->postJson("api/distribution_panetiers/{$this->productionPanetier->id}", $payload, $headers);
    }

    public function test_request_without_key_is_processed_normally()
    {
        $this->postDistribution($this->distributionPayload(100), null)->assertStatus(201);

        $this->assertDatabaseCount('distrib_panetiers', 1);
        $this->assertDatabaseCount('idempotency_keys', 0);
    }

    public function test_repeated_request_with_same_key_replays_response_without_executing_again()
    {
        $firstResponse = $this->postDistribution($this->distributionPayload(100), 'key-double-tap');
        $repeatedResponse = $this->postDistribution($this->distributionPayload(100), 'key-double-tap');

        $firstResponse->assertStatus(201);
        $repeatedResponse->assertStatus(201);
        $repeatedResponse->assertHeader('Idempotent-Replayed', 'true');
        $this->assertEquals($firstResponse->json(), $repeatedResponse->json());
        $this->assertDatabaseCount('distrib_panetiers', 1);
        $this->assertEquals(100, $this->livreur->fresh()->compteLivreur->solde_pain);
    }

    public function test_request_with_key_still_processing_returns_conflict()
    {
        $payload = $this->distributionPayload(100);
        // simulates the first request of a double tap still running on another process
        IdempotencyKey::create([
            'user_id' => $this->user->id,
            'idempotency_key' => 'key-in-progress',
            'request_fingerprint' => hash('sha256', 'POST|api/distribution_panetiers/'
                . $this->productionPanetier->id . '|' . json_encode($payload)),
        ]);

        $this->postDistribution($payload, 'key-in-progress')->assertStatus(409);

        $this->assertDatabaseCount('distrib_panetiers', 0);
    }

    public function test_same_key_with_different_payload_is_rejected()
    {
        $this->postDistribution($this->distributionPayload(100), 'key-reused')->assertStatus(201);

        $this->postDistribution($this->distributionPayload(250), 'key-reused')->assertStatus(422);

        $this->assertEquals(100, $this->productionPanetier->distribPanetiers()->sum('nombre_pain'));
    }

    public function test_failed_request_releases_key_so_corrected_form_can_be_submitted()
    {
        $invalidPayload = $this->distributionPayload(100);
        $invalidPayload['livreurs'][0]['livreur_id'] = 999999;

        $this->postDistribution($invalidPayload, 'key-after-error')->assertStatus(422);
        $this->assertDatabaseCount('idempotency_keys', 0);

        $this->postDistribution($this->distributionPayload(100), 'key-after-error')->assertStatus(201);
        $this->assertDatabaseCount('distrib_panetiers', 1);
    }

    public function test_same_key_from_another_user_is_independent()
    {
        $this->postDistribution($this->distributionPayload(100), 'shared-key')->assertStatus(201);

        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $response = $this->postDistribution($this->distributionPayload(100), 'shared-key');

        $response->assertStatus(201);
        $response->assertHeaderMissing('Idempotent-Replayed');
    }
}
