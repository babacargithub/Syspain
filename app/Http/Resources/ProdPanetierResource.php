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
        return [
            "id" => $this->id,
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
            //TODO change later

            "resultat"=>2340403,

            "chariots" => $this->chariots->map(function (ChariotProdPanetier $chariotProdPanetier) {
                return [
                    "nom" => $chariotProdPanetier->chariot->identifier(),
                    "nombre" => $chariotProdPanetier->nombre,
                    "nombre_pain"=> $chariotProdPanetier->chariot->nombre_pain
                ];
            }),
            "mange" => $this->mange,
        ];
    }
}
