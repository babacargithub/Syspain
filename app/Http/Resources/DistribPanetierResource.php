<?php

namespace App\Http\Resources;

use App\Models\Boulangerie;
use App\Models\DistribPanetier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistribPanetierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var $this DistribPanetier */
        $data = [
            "id" => $this->id,
            "nombre_pain" => $this->nombre_pain,

        ];
        if ($this->isForLivreur()) {
            $data["livreur"] = $this->livreur->identifier();
            $data["montant"] = $this->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_livreur;

        }
        if ($this->isForClient()) {
            $data["client"] = $this->client->identifier();
            $data["montant"] = $this->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client;
        }
        if ($this->isForBoutique()) {
            $data["boutique"] = $this->boutique->nom;
            $data["montant"] = $this->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client;
        }
        if ($this->isForAbonnement()) {
            $data["abonnement"] = $this->abonnement->identifier();
            $data["montant"] = $this->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client;
        }

        return  $data;
    }
}
