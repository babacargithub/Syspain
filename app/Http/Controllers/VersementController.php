<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersementResource;
use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\Client;
use App\Models\CompteLivreur;
use App\Models\DistribPanetier;
use App\Models\Livreur;
use App\Models\TypeRecette;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersementController extends Controller
{
    public function index()
    {
        return response()->json(Livreur::where('is_active',true)->get());

    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'montant' => 'required|numeric',
            'nombre_retour' => 'required|integer',
            'nombre_pain_matin' => 'integer',
            'distrib_panetier_id'=>'integer|exists:distrib_panetiers,id',
        ]);
        if (!isset($data['caisse_id'])){
            $data['caisse_id'] = Caisse::requireCaisseOfLoggedInUser()->id;
        }
        $caisse = Caisse::findOrFail($data['caisse_id']);

        $versement = new Versement($data);
        $montant_verse = $data['montant'];

        DB::transaction(function () use ($data, $versement, $montant_verse, $caisse) {
            $distrib_panetier = DistribPanetier::findOrFail($data['distrib_panetier_id']);
            $distrib_panetier->nombre_retour = $data['nombre_retour'];

           // set versement entity id based on distrib_panetier entity type
            if ($distrib_panetier->isForLivreur()){
                $data['livreur_id'] = $distrib_panetier->livreur_id;
                $versement->livreur_id = $distrib_panetier->livreur_id;
            }elseif ($distrib_panetier->isForClient()){
                $data['client_id'] = $distrib_panetier->client_id;
                $versement->client_id = $distrib_panetier->client_id;
            }elseif ($distrib_panetier->isForBoutique()){
                $data['boutique_id'] = $distrib_panetier->boutique_id;
                $versement->boutique_id = $distrib_panetier->boutique_id;
            }elseif ($distrib_panetier->isForAbonnement()){
                $data['abonnement_id'] = $distrib_panetier->abonnement_id;
                $versement->abonnement_id = $distrib_panetier->abonnement_id;
            }
            $versement->montant_verse = $data['montant'];

        $versement->nombre_retour = $data['nombre_retour'];
        $versement->date_versement = today()->toDateString();

        $versement->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        if ($versement->isForLivreur()){
            $livreur = Livreur::findOrFail($data['livreur_id']);
            $versement->livreur()->associate($livreur);
            // vérifier le montant versé par le livreur pour savoir s'il doit de l'argent ou on doit réduire son solde reliquat
            $compte_livreur = $livreur->compteLivreur;
            $compte_data = $compte_livreur->toArray();
            $nombre_pain_a_comptabiliser = $distrib_panetier->nombre_pain - $data['nombre_retour'];
            $montant_a_verser = $distrib_panetier->valeurPain();
            $montant_verse = $data['montant'];

            if ($montant_verse > $montant_a_verser) {
                $compte_livreur->solde_reliquat -= ($montant_verse - $montant_a_verser);
            }elseif ($montant_verse < $montant_a_verser){
                $compte_livreur->solde_reliquat += ($montant_a_verser - $montant_verse);
            }else if ($montant_verse == $montant_a_verser){
                $compte_livreur->solde_reliquat = 0;

            }
            $versement->compte_data = $compte_data;
            $versement->save();

            $compte_livreur->dette = $compte_livreur->dette - ($distrib_panetier->nombre_pain * $livreur->prix_pain);
            $compte_livreur->solde_pain = $compte_livreur->solde_pain - $distrib_panetier->nombre_pain;
            $compte_livreur->save();
            $identifier = $versement->identifier();
            $caisse->recettes()->create([
                'montant' => $montant_verse,

                'type_recette_id' => TypeRecette::ofCurrentBoulangerie()->where("constant_name",
                    TypeRecette::VERSEMENT_LIVREUR)
                    ->firstOrFail()->id,
                'commentaire' => 'Versement de ' . $identifier,
                'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id,
            ]);
            //save distrib panetier

        }elseif ($versement->isForClient()) {
             $versement->client()->associate($client = Client::find($data['client_id']));
            $compte_client = $versement->client->compteClient;
            $compte_client->dette = $compte_client->dette - ($distrib_panetier->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client);
            $compte_client->solde_pain = $compte_client->solde_pain - $distrib_panetier->nombre_pain;
            $compte_client->save();
            $caisse->recettes()->create([
                'montant' => $montant_verse,

                'type_recette_id' => TypeRecette::ofCurrentBoulangerie()->where("constant_name",
                    TypeRecette::VERSEMENT_CLIENT)
                    ->firstOrFail()->id,
                'commentaire' => 'Versement de ' . $client->identifier(),
                'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id,
            ]);
            // calculate reliquat

        }
        elseif ($versement->isForBoutique()) {
            $versement->boutique()->associate($boutique = Boutique::findOrFail($data['boutique_id']));
            $caisse->recettes()->create([
                'montant' => $montant_verse,

                'type_recette_id' => TypeRecette::ofCurrentBoulangerie()->where("constant_name",
                    TypeRecette::VENTE_BOUTIQUE)
                    ->firstOrFail()->id,
                'commentaire' => 'Vente Boutique  ' . $boutique->identifier(),
                'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id,
            ]);
        }
        elseif ($versement->isForAbonnement()) {
            $versement->abonnement()->associate($abonnement = Abonnement::findOrFail($data['abonnement_id']));
            $caisse->recettes()->create([
                'montant' => $montant_verse,

                'type_recette_id' => TypeRecette::ofCurrentBoulangerie()->where("constant_name",
                    TypeRecette::VERSEMENT_ABONNEMENT)
                    ->firstOrFail()->id,
                'commentaire' => 'Paiement Abonnement :  ' . $abonnement->identifier(),
                'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id,
            ]);
        }

        $versement->caisse()->associate($caisse);
        $versement->save();
        // augmenter le solde de la caisse
        $caisse->augmenterSolde($montant_verse);
        $caisse->save();

// Create the recette with the determined identifier

            $distrib_panetier->versement()->associate($versement);
            $distrib_panetier->save();


        });



        return response()->json($versement, 201);
    }
    // update versement
    public function update(Versement $versement)
    {
        $data = request()->validate([
            'montant' => 'numeric',
            'nombre_retour' => 'integer',
            'nombre_pain_matin' => 'integer',
            "caisse_id"=>"integer|exists:caisses,id",// 'retour' is a boolean field, so it should be
            "date_versement"=>"date|date_format:Y-m-d",// 'retour' is a boolean field, so it should be
            'livreur_id' => 'nullable|integer|exists:livreurs,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'abonnement_id' => 'nullable|integer|exists:abonnements,id',
            'boutique_id' => 'nullable|integer|exists:boutiques,id',
        ]);

        DB::transaction(function () use ($data, $versement) {
            $versement->montant_verse = $data['montant'];
            $versement->nombre_retour = $data['nombre_retour'];
            $versement->date_versement = $data['date_versement'];
            $versement->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;

            $livreur = Livreur::findOrFail($data['livreur_id']);
            $versement->livreur()->associate($livreur);
            $versement->caisse()->associate(Caisse::find($data['caisse_id']));

            $versement->save();

            // vérifier le montant versé par le livreur pour savoir s'il doit de l'argent ou on doit réduire son solde reliquat
            $compte_livreur = $livreur->compteLivreur;
            $nombre_pain_a_comptabiliser = $compte_livreur->solde_pain - $data['nombre_retour'];
            $montant_a_verser = $nombre_pain_a_comptabiliser * $livreur->prix_pain;
            $montant_verse = $data['montant'];

            if ($montant_verse > $montant_a_verser) {
                $compte_livreur->solde_reliquat -= ($montant_verse - $montant_a_verser);
            } elseif ($montant_verse < $montant_a_verser) {
                $compte_livreur->solde_reliquat += ($montant_a_verser - $montant_verse);
            }
            $compte_data = $compte_livreur->toArray();
            $versement->compte_data = $compte_data;
        });

    }
    public function destroy(Versement $versement)
    {

        DB::transaction(function () use ($versement) {
            $livreur = $versement->livreur;
            $compte_livreur = $livreur->compteLivreur;
            $compte_livreurWhenVersementWasMade = new CompteLivreur($versement->compte_data);

            $compte_livreur->augmenterDette($compte_livreurWhenVersementWasMade->dette);
            $compte_livreur->augmenterSoldePain($compte_livreurWhenVersementWasMade->solde_pain);
            // check if we should increase or decrease the solde reliquat
            $nombre_pain_a_comptabiliser = $compte_livreur->solde_pain - $versement->nombre_retour;
            $montant_a_verser = $nombre_pain_a_comptabiliser * $livreur->prix_pain;
            $montant_verse = $versement->montant_verse;
            $compte_livreur->save();
            /** @var  $caisse Caisse */
            $caisse = Caisse::find($versement->caisse_id);
            $solde_avant = $caisse->solde;
            $caisse->diminuerSolde($montant_verse);
            $caisse->transactions()->create([
                'montant' => $montant_verse,
                'type' => 'cashout',
                "solde_apres" => $caisse->solde + $montant_verse,
                "solde_avant" => $solde_avant,

                'commentaire' => 'Suppression du versement'
            ]);
            $caisse->save();
            $versement->delete();

        });
        return response()->json(null, 204);

    }

    public function versementsLivreurs()
    {
        Versement::factory()->count(10)->create();
        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();
        $livreurs = $boulangerie->livreurs()->with('versements')->where('is_active',true)->get();

        return response()->json($livreurs);

    }
    public function versementsDate($date){

        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();
        // group versements by livreur, client, boutique, abonnement
        $livreurs = $boulangerie->versements()->whereDate('created_at',$date)->where('livreur_id','!=',null)
            ->orderByDesc('created_at')
            ->get();
        $clients = $boulangerie->versements()->whereDate('created_at',$date)->where('client_id','!=',null)
            ->orderByDesc('created_at')
            ->get();
        $abonnements = $boulangerie->versements()->whereDate('created_at',$date)->where('abonnement_id','!=',null)
            ->orderByDesc('created_at')
            ->get();
        $boutiques = $boulangerie->versements()->whereDate('created_at',$date)->where('boutique_id','!=',null)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'livreurs' => VersementResource::collection($livreurs),
            'clients' => VersementResource::collection($clients),
            'abonnements' => VersementResource::collection($abonnements),
            'boutiques' => VersementResource::collection($boutiques),
        ]);
    }
    public function destinations()
    {
        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();
        $livreurs = $boulangerie->livreurs()->where('is_active',true)->get();
        $clients = $boulangerie->clients;
        $abonnements = $boulangerie->abonnements;
        $boutiques = $boulangerie->boutiques;
        return response()->json([
            'livreurs' => $livreurs->map(function (Livreur $livreur){
                return [
                    'id' => $livreur->id,
                    'nom' => $livreur->identifier(),
                    'solde_pain' => $livreur->compteLivreur->solde_pain,
                    'solde_reliquat' => $livreur->compteLivreur->solde_reliquat,
                    'dette' => $livreur->compteLivreur->dette,
                ];
            }),
            'clients' => $clients
//                ->filter(function (Client $client){
//                    return $client->abonnement == null;
//                })
                ->map(function (Client $client){
                return [
                    'id' => $client->id,
                    'nom' => $client->identifier(),
                    'solde_pain' => $client->compteClient->solde_pain,
                    'dette' => $client->compteClient->dette,
                ];
            }),
            'abonnements' => $abonnements->map(function (Abonnement $abonnement){
                return [
                    'id' => $abonnement->id,
                    'nom' => $abonnement->identifier(),
                    'solde_pain' => $abonnement->solde_pain,
                    'dette' => $abonnement->dette,
                ];
            }),
            'boutiques' => $boutiques->map(function (Boutique $boutique){
                return [
                    'id' => $boutique->id,
                    'nom' => $boutique->identifier(),
                    'solde_pain' => $boutique->solde_pain,
                ];
            }),
        ]);

    }
}
