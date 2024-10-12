<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\DistribPanetier;
use App\Models\Versement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Livreur;

class LivreurController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        $livreurs = Livreur::whereBoulangerieId(Boulangerie::requireBoulangerieOfLoggedInUser()->id)->whereIsActive
        (true)->get()->map(function ($livreur) {
            return [
                'id' => $livreur->id,
                'prenom' => $livreur->prenom,
                'nom' => $livreur->nom,
                'telephone' => $livreur->telephone,
                'prix_pain' => $livreur->prix_pain,
                'is_active' => (bool)$livreur->is_active,
                'identifier' => $livreur->identifier(),
                'solde_reliquat' => $livreur->compteLivreur->solde_reliquat,
                'solde_pain' => $livreur->compteLivreur->solde_pain,
                'dette' => $livreur->compteLivreur->dette,

            ];
        });
        return response()->json($livreurs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'telephone' => 'required|numeric|digits:9|unique:livreurs,telephone',
            // Add other fields as necessary
        ], [
            'telephone.unique' => 'Le numéro de téléphone est déjà utilisé',
        ]);

        $livreur = new Livreur($validatedData);
        $livreur->is_active = true;
        $livreur->prix_pain = Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_livreur;
        $livreur->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $livreur->save();
        $livreur->compteLivreur()->create();
        return response()->json($livreur, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Livreur  $livreur
     * @return JsonResponse
     */
    public function update(Request $request, Livreur $livreur)
    {
        $validatedData = $request->validate([
            'prenom' => 'string|max:255',
            'nom' => 'string|max:255',
            'telephone' => 'string|digits:9|unique:livreurs,telephone,'.$livreur->id,
            // Add other fields as necessary
        ]);

        $livreur->update($validatedData);
        return response()->json($livreur);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Livreur  $livreur
     * @return JsonResponse
     */
    public function destroy(Livreur $livreur)
    {
        $livreur->delete();
        return response()->json(null, 204);
    }

    /**
     * Disable the specified resource.
     *
     * @param Livreur $livreur
     * @param bool $is_active
     * @return JsonResponse
     */
    public function disable(Livreur $livreur, bool $is_active = false)
    {

        $livreur->update(['is_active' => $is_active]);
        return response()->json(['message' => 'Livreur disabled successfully']);
    }

    public function historique(Livreur $livreur)
    {
        // Get the distribPanetiers related to the current livreur
        $distribPanetiers = DistribPanetier::select([
            'nombre_pain','created_at','bonus'
        ])->with('productionPanetier')
            ->where('livreur_id', $livreur->id)
            ->get();

        // Get the versements related to the current livreur
        $versements = Versement::select(['montant_verse','date_versement','nombre_retour'])->where('livreur_id',
            $livreur->id)
            ->get();

        // Calculate the total pain taken
        $totalPainTaken = $distribPanetiers->sum('nombre_pain');

        // Calculate the total amount of versements
        $totalVersements = $versements->sum('montant_verse');

        // Calculate solde reliquat and solde pain
        $soldeReliquat = $livreur->compteLivreur->solde_reliquat;
        $soldePain = $livreur->compteLivreur->solde_pain;

        return response()->json([
            'distribPanetiers' => $distribPanetiers,
            'versements' => $versements,
            'totalPainTaken' => $totalPainTaken,
            'totalVersements' => $totalVersements,
            'soldeReliquat' => $soldeReliquat,
            "dette"=> $soldePain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_livreur,
            'soldePain' => $soldePain,
        ]);
    }

    // get list of 30 distrib_panetiers of livreurs
    public function getDistribPanetiersOfLivreurs(Livreur $livreur)
    {


    }

}