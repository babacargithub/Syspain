<?php

namespace App\Http\Resources;

use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersementResource extends  JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var $this Versement */
        $data = [
            'id' => $this->id,
            "montant_verse" => $this->montant_verse,
            "nombre_retour" => $this->nombre_retour,
            ];
        if ($this->isForLivreur()){
            $data['livreur'] = $this->livreur->identifier();
        }
        if ($this->isForClient()){
            $data['client'] = $this->client->identifier();
        }
        if ($this->isForBoutique()){
            $data['boutique'] = $this->boutique->identifier();
        }
        if ($this->isForAbonnement()){
            $data['abonnement'] = $this->abonnement->identifier();
        }
        $data['caisse'] = $this->caisse->identifier();
        return $data;
    }


}