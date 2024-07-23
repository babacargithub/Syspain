<?php

namespace App\Http\Controllers;

use App\Http\Resources\VersementResource;
use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\Client;
use App\Models\CompteLivreur;
use App\Models\Livreur;
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
            'nombre_pain_matin' => 'required|integer',
            "caisse_id"=>"integer|exists:caisses,id",// 'retour' is a boolean field, so it should be
            "date_versement"=>"required|date|date_format:Y-m-d",// 'retour' is a boolean field, so it should be
            'livreur_id' => 'integer|exists:livreurs,id',
            'client_id' => 'integer|exists:clients,id',
            'abonnement_id' => 'integer|exists:abonnements,id',
            'boutique_id' => 'integer|exists:boutiques,id',
        ]);
        if (!isset($data['caisse_id'])){
            $data['caisse_id'] = Caisse::requireCaisseOfLoggedInUser()->id;
        }
        // if neither livreur_id, client_id, abonnement_id, boutique_id is set, then it's a 422 error
        if (!isset($data['livreur_id']) && !isset($data['client_id']) && !isset($data['abonnement_id']) && !isset($data['boutique_id'])){
            return response()->json(['message' => 'Vous devez choisir un livreur, un client, un abonnement ou une 
            boutique'], 422);
        }
        $versement = new Versement($data);
        $montant_verse = $data['montant'];

        DB::transaction(function () use ($data, $versement, $montant_verse) {

        $versement->montant_verse = $data['montant'];
        $versement->nombre_retour = $data['nombre_retour'];
        $versement->date_versement = $data['date_versement'];

        $versement->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        if ($versement->isForLivreur()){
            $livreur = Livreur::findOrFail($data['livreur_id']);
            $versement->livreur()->associate($livreur);

            // vérifier le montant versé par le livreur pour savoir s'il doit de l'argent ou on doit réduire son solde reliquat
            $compte_livreur = $livreur->compteLivreur;
            $nombre_pain_a_comptabiliser = $compte_livreur->solde_pain - $data['nombre_retour'];
            $montant_a_verser = $nombre_pain_a_comptabiliser * $livreur->prix_pain;
            $montant_verse = $data['montant'];

            if ($montant_verse > $montant_a_verser) {
                $compte_livreur->solde_reliquat -= ($montant_verse - $montant_a_verser);
            }elseif ($montant_verse < $montant_a_verser){
                $compte_livreur->solde_reliquat += ($montant_a_verser - $montant_verse);
            }
            $compte_data = $compte_livreur->toArray();
            $versement->compte_data = $compte_data;
            $versement->save();

            $compte_livreur->dette = 0;
            $compte_livreur->solde_pain = 0;
            $compte_livreur->save();
        }elseif ($versement->isForClient()) {
            $versement->client()->associate(Client::find($data['client_id']));
            $compte_client = $versement->client->compteClient;
            $compte_client->dette = 0;
            $compte_client->solde_pain = 0;
            $compte_client->save();
            // calculate reliquat

        }
        elseif ($versement->isForBoutique()) {
            $versement->boutique()->associate(Boutique::find($data['boutique_id']));
        }
        elseif ($versement->isForAbonnement()) {
            $versement->abonnement()->associate(Abonnement::find($data['abonnement_id']));
        }

        $versement->caisse()->associate(Caisse::find($data['caisse_id']));
        $versement->save();

        $caisse = Caisse::find($data['caisse_id']);
        $caisse->augmenterSolde($montant_verse);
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

            // TODO recalculer reliquat
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
}
