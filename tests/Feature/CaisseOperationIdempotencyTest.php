<?php

namespace Tests\Feature;

use App\Models\Depense;
use App\Models\TypeDepense;
use App\Models\VersementBanque;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A repeated caisse operation (double tap, retry) sent with the same Idempotency-Key
 * must move the caisse solde only once.
 */
class CaisseOperationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function createDepensePayload(): array
    {
        $typeDepense = TypeDepense::factory()->create(['boulangerie_id' => $this->boulangerie->id]);
        return ['type_depense_id' => $typeDepense->id, 'montant' => 10000, 'commentaire' => 'Achat farine'];
    }

    public function test_repeated_depense_creation_debits_caisse_once()
    {
        $payload = $this->createDepensePayload();
        $headers = ['Idempotency-Key' => 'depense-double-tap'];

        $this->postJson('/api/depenses', $payload, $headers)->assertStatus(201);
        $this->postJson('/api/depenses', $payload, $headers)
            ->assertStatus(201)
            ->assertHeader('Idempotent-Replayed', 'true');

        $this->assertEquals(1, Depense::count());
        $this->assertEquals(-10000, $this->caisse->fresh()->solde);
    }

    public function test_repeated_depense_deletion_credits_caisse_once()
    {
        $depenseIdentifier = $this->postJson('/api/depenses', $this->createDepensePayload())->json('id');
        $headers = ['Idempotency-Key' => "delete-depenses-{$depenseIdentifier}"];

        $this->deleteJson("/api/depenses/{$depenseIdentifier}", [], $headers)->assertStatus(204);
        // without the key this second call would be a 404: the client now gets the same success back
        $this->deleteJson("/api/depenses/{$depenseIdentifier}", [], $headers)
            ->assertStatus(204)
            ->assertHeader('Idempotent-Replayed', 'true');

        $this->assertEquals(0, $this->caisse->fresh()->solde);
    }

    public function test_repeated_versement_banque_debits_caisse_once()
    {
        $payload = ['montant' => 50000, 'banque' => 'CBAO'];
        $headers = ['Idempotency-Key' => 'versement-banque-double-tap'];

        $firstResponse = $this->postJson('/api/versements_banques', $payload, $headers);
        $repeatedResponse = $this->postJson('/api/versements_banques', $payload, $headers);

        $this->assertTrue($firstResponse->isSuccessful());
        $this->assertEquals($firstResponse->getStatusCode(), $repeatedResponse->getStatusCode());
        $repeatedResponse->assertHeader('Idempotent-Replayed', 'true');
        $this->assertEquals(1, VersementBanque::count());
        $this->assertEquals(-50000, $this->caisse->fresh()->solde);
    }
}
