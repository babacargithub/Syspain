<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\Client;
use App\Models\DistribPanetier;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribPanetierController extends Controller
{
    public function store(ProductionPanetier $productionPanetier, Request $request)
    {
        $data = $request->validate([
            'nombre_pain' => 'required|integer',
            'livreur_id' => 'nullable|integer|exists:livreurs,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'abonnement_id'=> 'nullable|integer|exists:abonnements,id',
            "boutique_id" => "nullable|integer|exists:boutiques,id",
            "paye"=> "boolean",
            'production_panetier_id' => 'integer|exists:production_panetiers,id',]);
        if ($data['nombre_pain'] > $productionPanetier->nombre_pain) {
            return response()->json(["message" => "Le nombre de pain distribué ne peut pas être supérieur au nombre de pain produit"], 422);
        }
        else if ($data['nombre_pain'] < 0) {
            return response()->json(["message" => "Le nombre de pain distribué ne peut pas être négatif"], 422);
        }
        else if ($data['nombre_pain'] > ($productionPanetier->nombre_pain -
                $productionPanetier->total_pain_distribue)) {
            return response()->json(["message" => "Le nombre de pain distribué ne peut pas être supérieur au nombre de pain restant"], 422);
        }
        $distribPanetier = new DistribPanetier($data);
        // start transaction before saving operations
        DB::transaction(function () use ($productionPanetier, $distribPanetier) {
            $productionPanetier->distribPanetiers()->save($distribPanetier);
            // if it's a client, we need to update the client's account
            if ($distribPanetier->isForClient()) {
                $client = Client::find($distribPanetier->client_id);
                $compte_client = $client->compteClient;
                $compte_client->solde_pain += $distribPanetier->nombre_pain;
                $compte_client->dette = $compte_client->dette + ($distribPanetier->nombre_pain *
                        $productionPanetier->prix_pain_client);

                $compte_client->save();
            }
            // if it's a livreur, we need to update the livreur  account
            if ($distribPanetier->isForLivreur()) {
                $livreur = Livreur::find($distribPanetier->livreur_id);
                $compte_livreur = $livreur->compteLivreur;
                $compte_livreur->solde_pain += $distribPanetier->nombre_pain;
                $compte_livreur->dette = $compte_livreur->dette + ($distribPanetier->nombre_pain *
                        $productionPanetier->prix_pain_livreur);
                $compte_livreur->save();
            }
            // if it's a boutique, we need to update the boutique's account
            if ($distribPanetier->isForBoutique()) {
                $boutique = Boutique::find($distribPanetier->boutique_id);
                $boutique->solde_pain += $distribPanetier->nombre_pain;
                $boutique->save();
            }
            // if it's an abonnement, we need to update the abonnement account
            if ($distribPanetier->isForAbonnement()) {
                $abonnement = Abonnement::find($distribPanetier->abonnement_id);
                $abonnement->solde_pain += $distribPanetier->nombre_pain;
                $abonnement->dette = $abonnement->dette + ($distribPanetier->nombre_pain *
                        $productionPanetier->prix_pain_client);
                $abonnement->save();
            }

        });



        return response()->json($distribPanetier, 201);

    }

    public function update(DistribPanetier $distribPanetier, Request $request)
    {
        $data = $request->validate([
            'nombre_pain' => 'integer',
            'livreur_id' => 'nullable|integer|exists:livreurs,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'abonnement_id'=> 'nullable|integer|exists:abonnements,id',
            "boutique_id" => "nullable|integer|exists:boutiques,id",
            'production_panetier_id' => 'integer|exists:production_panetiers,id',]);

        $distribPanetier->update($data);
        return response()->json($distribPanetier);
    }

    public function destroy(DistribPanetier $distribPanetier)
    {
        $distribPanetier->delete();
        return response()->json(null, 204);
    }

    // show distrib panetier
    public function show(DistribPanetier $distribPanetier)
    {
        return response()->json($distribPanetier);
    }


}
