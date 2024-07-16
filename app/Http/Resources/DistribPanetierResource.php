<?php

namespace App\Http\Resources;

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
        }
        if ($this->isForClient()) {
            $data["client"] = $this->client->identifier();
        }
        if ($this->isForBoutique()) {
            $data["boutique"] = $this->boutique->nom;
        }
        if ($this->isForAbonnement()) {
            $data["abonnement"] = $this->abonnement->identifier();
        }

        return  $data;
    }
}
