<?php

namespace App\Http\Resources;

use App\Models\Depense;
use App\Models\Recette;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OperationCaisseResource extends  JsonResource
{
    public function toArray(Request $request): array
    {
        $definition = [
            'id' => $this->id,
            'montant' => $this->montant,
            'created_at' => $this->created_at,
            'commentaire' => $this->commentaire
        ];
        if (isset($this->typeRecette)) {
            $definition['identifier'] = $this->identifier();
            $definition['type_recette_nom'] = $this->typeRecette->nom;
            $definition['type_recette_id'] = $this->typeRecette->id;
        }
        else if (isset($this->typeDepense)) {
            $definition['depense'] = $this->typeDepense->nom;
            $definition['type_depense_nom'] = $this->typeDepense->nom;
            $definition['type_depense_id'] = $this->typeDepense->id;
        }else{
            // throw exception
            throw new \Exception("OperationCaisseResource: OperationCaisseResource must be either Recette or Depense");

        }

        return $definition;
    }
}