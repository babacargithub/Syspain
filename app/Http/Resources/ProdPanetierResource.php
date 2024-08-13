<?php

namespace App\Http\Resources;

use App\Models\ChariotProdPanetier;
use App\Models\ProductionPanetier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdPanetierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var $this ProductionPanetier */
        $definition = [
            "id" => $this->id,
            "nombre_petrisseur" => $this->prodPetrisseur !== null ?
                $this->prodPetrisseur->total_pain : 0,
            "date_production" => $this->date_production,
            "identifier" => $this->identifier(),
            "nombre_pain" => $this->nombre_pain,
            "nombre_plat" => $this->nombre_plat,
            "nombre_sac" => $this->nombre_sac,
            "ration" => $this->ration,
            "donation" => $this->donation,
            "casse" => $this->casse,
            "total_pain_petrisseur_produit" => $this->total_pain_petrisseur_produit,
            "nombre_pain_entregistre" => $this->nombre_pain_entregistre,
            "total_pain_distribue" => $this->total_pain_distribue,

            "chariots" => $this->chariots->map(function (ChariotProdPanetier $chariotProdPanetier) {
                return [
                    "nom" => $chariotProdPanetier->chariot->identifier(),
                    "nombre" => $chariotProdPanetier->nombre,
                    "nombre_pain"=> $chariotProdPanetier->chariot->nombre_pain
                ];
            }),
            "mange" => $this->mange,
        ];
        $definition['resultat'] = $definition['nombre_petrisseur'] - $definition['nombre_pain_entregistre'];
        return $definition;
    }
}
