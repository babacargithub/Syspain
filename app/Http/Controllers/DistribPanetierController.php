<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Boulangerie;
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
        // the request data should have the following fields livreurs array, clients array, abonnements array, boutiques
        // array. The livreurs array should have the following fields: livreur_id, nombre_pain, bonus, the same for
        // clients, abonnements, and boutiques
        $data = $request->validate([
            'livreurs' => 'array',
            'clients' => 'array',
            'abonnements' => 'array',
            'boutiques' => 'array',
            'livreurs.*.livreur_id' => 'required|integer|exists:livreurs,id',
            'livreurs.*.nombre_pain' => 'required|integer',
            'livreurs.*.bonus' => 'required|integer',
            'clients.*.client_id' => 'required|integer|exists:clients,id',
            'clients.*.nombre_pain' => 'required|integer',
            'clients.*.bonus' => 'required|integer',
            'abonnements.*.abonnement_id' => 'required|integer|exists:abonnements,id',
            'abonnements.*.nombre_pain' => 'required|integer',
            'abonnements.*.bonus' => 'required|integer',
            'boutiques.*.boutique_id' => 'required|integer|exists:boutiques,id',
            'boutiques.*.nombre_pain' => 'required|integer',
            'boutiques.*.bonus' => 'required|integer',

        ]);
        // check if the sum of all the pain distributed is equal to the pain produced
        // $data['nombre_pain'] is the sum of livreurs, clients, abonnements, and boutiques in request
        // we must first sum the existing distribPanetiers  in order to take into account the update
        // of the distribPanetier

        // add the 4 values
        $totalPainOfExisting = $productionPanetier->distribPanetiers()
            ->where(function($query) use ($data) {
                $query->whereIn('livreur_id', collect($data['livreurs'])->pluck('livreur_id')->toArray())
                    ->orWhereIn('client_id', collect($data['clients'])->pluck('client_id')->toArray())
                    ->orWhereIn('abonnement_id', collect($data['abonnements'])->pluck('abonnement_id')->toArray())
                    ->orWhereIn('boutique_id', collect($data['boutiques'])->pluck('boutique_id')->toArray());
            })->sum('nombre_pain');
//

        $data['nombre_pain'] =
            collect($data['livreurs'])->sum('nombre_pain') +
            collect($data['clients'])->sum('nombre_pain') +
            collect($data['abonnements'])->sum('nombre_pain') +
            collect($data['boutiques'])->sum('nombre_pain') - $totalPainOfExisting;
        // loop through livreurs and create a distribPanetier for each

        if ($data['nombre_pain'] > $productionPanetier->nombre_pain) {
            return response()->json(["message" => "Le nombre de pain distribué ne peut pas être supérieur au nombre de pain produit"], 422);
        } else if ($data['nombre_pain'] < 0) {
            return response()->json(["message" => "Le nombre de pain distribué ne peut pas être négatif"], 422);
        } else if ($data['nombre_pain'] > ($productionPanetier->nombre_pain_entregistre - $totalPainOfExisting)) {
            return response()->json(["message" => "Pour le total de pain que vous voulez enregistrer le nombre de pain restant est insuffisant !"], 422);
        }
        // start transaction before saving operations
        DB::transaction(function () use ($productionPanetier, $data) {
            $prix_pain_livreur = Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_livreur;
            $prix_pain_client = Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client;
            // loop through livreurs and create a distribPanetier for each
            foreach ($data['livreurs'] as $distrib_data) {


                $distribPanetier = $productionPanetier->distribPanetiers()->whereLivreurId($distrib_data['livreur_id'])
                    ->first();
                if ($distribPanetier == null) {
                    $distribPanetier = new DistribPanetier([
                        'livreur_id' => $distrib_data['livreur_id'],
                        'nombre_pain' => $distrib_data['nombre_pain'],
                        'bonus' => $distrib_data['bonus'],
                    ]);
                    $productionPanetier->distribPanetiers()->save($distribPanetier);
                    $livreur = $distribPanetier->livreur;
                    $compte_livreur = $livreur->compteLivreur;
                    $compte_livreur->augmenterSoldePain($distrib_data['nombre_pain']);
                    $compte_livreur->augmenterDette(($distrib_data['nombre_pain'] *
                            $livreur->prix_pain));
                    $compte_livreur->save();
                } else {
                    $oldNombrePain = $distribPanetier->nombre_pain;
                    $distribPanetier->nombre_pain = $distrib_data['nombre_pain'];
                    $distribPanetier->bonus = $distrib_data['bonus'];
                    $distribPanetier->save();
                    // update compte livreur
                    $livreur = $distribPanetier->livreur;
                    $compte_livreur = $livreur->compteLivreur;
                    // we check the difference between the new nombre_pain and the old one
                    $diff = $distrib_data['nombre_pain'] - $oldNombrePain;
                    if ($diff > 0) {
                        $compte_livreur->augmenterSoldePain($diff);
                        $compte_livreur->augmenterDette( ($diff *
                               $livreur->prix_pain));
                    } else if ($diff < 0) {
                        $compte_livreur->diminuerSoldePain(abs($diff));
                        $compte_livreur->diminuerDette((abs($diff) *
                                $livreur->prix_pain));
                    }
                    $compte_livreur->save();
                }

            }
//            $productionPanetier->distribPanetiers()->save($distribPanetier);
            // loop through clients and create a distribPanetier for each
            foreach ($data['clients'] as $client_data) {
                $distribPanetier = $productionPanetier->distribPanetiers()->whereClientId($client_data['client_id'])
                    ->first();
                if ($distribPanetier == null) {
                    $distribPanetier = new DistribPanetier([
                        'client_id' => $client_data['client_id'],
                        'nombre_pain' => $client_data['nombre_pain'],
                    ]);
                    $productionPanetier->distribPanetiers()->save($distribPanetier);
                    $client = Client::find($client_data['client_id']);
                    $compte_client = $client->compteClient;
                    $compte_client->solde_pain += $client_data['nombre_pain'];
                    $compte_client->dette += ($client_data['nombre_pain'] *
                            $prix_pain_client);
                    $compte_client->save();
                } else {
                    $oldNombrePain = $distribPanetier->nombre_pain;
                    $distribPanetier->nombre_pain = $client_data['nombre_pain'];
                    $distribPanetier->bonus = $client_data['bonus'];
                    $distribPanetier->save();
                    $client = Client::find($client_data['client_id']);
                    $compte_client = $client->compteClient;
                    $diff = $client_data['nombre_pain'] - $oldNombrePain;
                    if ($diff > 0) {
                        $compte_client->solde_pain += $diff;
                        $compte_client->dette += ($diff *
                                $prix_pain_client);
                    } else if ($diff < 0) {
                        $compte_client->solde_pain -= abs($diff);
                        $compte_client->dette -= (abs($diff) *
                                $prix_pain_client);
                    }
                    $compte_client->save();
                }

            }
            // loop through abonnements and create a distribPanetier for each
            foreach ($data['abonnements'] as $abonnement_data) {
                $distribPanetier = $productionPanetier->distribPanetiers()->whereAbonnementId($abonnement_data['abonnement_id'])
                    ->first();
                    $abonnement = Abonnement::find($abonnement_data['abonnement_id']);
                if ($distribPanetier == null) {
                    $distribPanetier = new DistribPanetier([
                        'abonnement_id' => $abonnement_data['abonnement_id'],
                        'nombre_pain' => $abonnement_data['nombre_pain'],
                    ]);
                    $productionPanetier->distribPanetiers()->save($distribPanetier);
                    $abonnement->solde_pain += $abonnement_data['nombre_pain'];
                    $abonnement->dette += ($abonnement_data['nombre_pain'] *
                        $productionPanetier->prix_pain_client);
                } else {
                    $oldNombrePain = $distribPanetier->nombre_pain;
                    $distribPanetier->nombre_pain = $abonnement_data['nombre_pain'];
                    $distribPanetier->bonus = $abonnement_data['bonus'];
                    $distribPanetier->save();
                    $abonnement->solde_pain += $abonnement_data['nombre_pain'];
                    $diff = $abonnement_data['nombre_pain'] - $oldNombrePain;
                    if ($diff > 0) {
                        $abonnement->dette += ($diff *
                            $prix_pain_client);
                    } else if ($diff < 0) {
                        $abonnement->dette -= (abs($diff) *
                            $prix_pain_client);
                    }
                }
                $abonnement->save();

            }
            // loop through boutiques and create a distribPanetier for each
            foreach ($data['boutiques'] as $boutique_data) {
                $distribPanetier = $productionPanetier->distribPanetiers()->whereBoutiqueId($boutique_data['boutique_id'])
                    ->first();
                    $boutique = Boutique::find($boutique_data['boutique_id']);
                if ($distribPanetier == null) {
                    $distribPanetier = new DistribPanetier([
                        'boutique_id' => $boutique_data['boutique_id'],
                        'nombre_pain' => $boutique_data['nombre_pain'],
                    ]);
                    $boutique->solde_pain += $boutique_data['nombre_pain'];
                    $boutique->save();
                    $productionPanetier->distribPanetiers()->save($distribPanetier);
                } else {
                    $oldNombrePain = $distribPanetier->nombre_pain;
                    $distribPanetier->nombre_pain = $boutique_data['nombre_pain'];
                    $distribPanetier->bonus = $boutique_data['bonus'];
                    $distribPanetier->save();
                    $diff = $boutique_data['nombre_pain'] - $oldNombrePain;
                    if ($diff > 0) {
                        $boutique->solde_pain += $diff;
                    } else if ($diff < 0) {
                        $boutique->solde_pain -= abs($diff);
                    }
                    $boutique->save();
                }

            }


        });


        return response()->json($data, 201);

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

    public function getEntitiesForDistrib(Request $request, ProductionPanetier $productionPanetier)
    {
        $boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;

        $clients = Client::whereBoulangerieId($boulangerie_id)->get()->map(function (Client $client) use ($productionPanetier) {
            $data = [
                'id' => $client->id,
                'nom' => $client->identifier()

            ];
            $distribClient = $productionPanetier->distribPanetiers()->whereClientId($client->id)->first();
            if ($distribClient) {
                $data['nombre_pain'] = $distribClient->nombre_pain;
                $data['bonus'] = $distribClient->bonus;
            }
            return $data;
        });
        $livreurs = Livreur::whereBoulangerieId($boulangerie_id)->whereIsActive(true)
            ->get()->map(function (Livreur $livreur) use ($productionPanetier) {
                $data = [
                    'id' => $livreur->id,
                    'nom' => $livreur->identifier()
                ];
                $distribLivreur = $productionPanetier->distribPanetiers()->whereLivreurId($livreur->id)->first();
                if ($distribLivreur) {
                    $data['nombre_pain'] = $distribLivreur->nombre_pain;
                    $data['bonus'] = $distribLivreur->bonus;
                }
                return $data;
            });
        $abonnements = Abonnement::whereHas("client", function ($query) use ($boulangerie_id) {
            $query->where("boulangerie_id", $boulangerie_id);

        })->get()->map(function (Abonnement $abonnement) use ($productionPanetier) {
            $data = [
                'id' => $abonnement->id,
                'nom' => $abonnement->identifier()
            ];
            $distribAbonnement = $productionPanetier->distribPanetiers()->whereAbonnementId($abonnement->id)->first();
            if ($distribAbonnement) {
                $data['nombre_pain'] = $distribAbonnement->nombre_pain;
                $data['bonus'] = $distribAbonnement->bonus;
            }
            return $data;
        });
        $boutiques = Boutique::whereBoulangerieId($boulangerie_id)->orderBy('id')
            ->get(['id', 'nom'])->map(function (Boutique $boutique) use ($productionPanetier) {
                $data = [
                    'id' => $boutique->id,
                    'nom' => $boutique->nom
                ];
                $distribBoutique = $productionPanetier->distribPanetiers()->whereBoutiqueId($boutique->id)->first();
                if ($distribBoutique) {
                    $data['nombre_pain'] = $distribBoutique->nombre_pain;
                    $data['bonus'] = $distribBoutique->bonus;
                }
                return $data;
            });

        return response()->json([
            'clients' => $clients,
            'livreurs' => $livreurs,
            'abonnements' => $abonnements,
            'boutiques' => $boutiques,
        ]);
    }


}
