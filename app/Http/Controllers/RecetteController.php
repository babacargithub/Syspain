<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\Recette;
use App\Models\TypeRecette;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecetteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $recettes = Recette::with('typeRecette')->whereCaisseId(Caisse::requireCaisseOfLoggedInUser()->id)->get()->map
        (function ($recette) {
            return [
                'id' => $recette->id,
                'identifier' => $recette->identifier(),
                "montant" => $recette->montant,
                "created_at" => $recette->created_at,

            ];
        });
        return response()->json($recettes);
    }

    public function recettesJour($date)
    {
        $recettes = Recette::with('typeRecette')->whereCaisseId(Caisse::requireCaisseOfLoggedInUser()->id)->whereDate('created_at', $date)->get()->map
        (function ($recette) {
            return [
                'id' => $recette->id,
                'identifier' => $recette->identifier(),
                "montant" => $recette->montant,
                "created_at" => $recette->created_at,

            ];
        });
        return response()->json($recettes);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:10',
            'type_recette_id' => 'required|exists:type_recettes,id',
            'commentaire' => 'nullable|string',
            "caisse_id" => "required|exists:caisses,id",
            // Add other fields as necessary
        ]);

        $recette = new Recette($validated);
        $recette->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        DB::transaction(function () use ($recette) {
            $recette->save();
            // TODO: check the right caisse
            $caisse = Caisse::requireCaisseOfLoggedInUser();
            $caisse->augmenterSolde($recette->montant);
        });
        $recette->refresh();
        return response()->json($recette, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Recette $recette
     * @return JsonResponse
     */
    public function show(Recette $recette)
    {
//        $recette->load('typeRecette');
        return response()->json([
            'id' => $recette->id,
            'montant' => $recette->montant,
            'commentaire' => $recette->commentaire,
            'created_at' => $recette->created_at,
            'identifier' => $recette->identifier(),

        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Recette $recette
     * @return JsonResponse
     */
    public function destroy(Recette $recette)
    {
        DB::transaction(function () use ($recette) {
            $recette->delete();
            // TODO: check the right caisse
            $caisse = Caisse::requireCaisseOfLoggedInUser();
            $caisse->diminuerSolde($recette->montant);
        });

        return response()->json(null, 204);
    }
}
   /* public function storeTypeRecette()
    {
        $validated = request()->validate([
            'nom' => 'required|string|max:255',
            // Add other fields as necessary
        ]);

        $typeRecette = TypeRecette::create($validated);
        return response()->json($typeRecette, 201);

    }
    public function updateTypeRecette(TypeRecette $typeRecette)
    {
        $validated = request()->validate([
            'nom' => 'required|string|max:255',
            // Add other fields as necessary
        ]);

        $typeRecette->update($validated);
        return response()->json($typeRecette);
    }
    public function destroyTypeRecette(TypeRecette $typeRecette)
    {
        $typeRecette->delete();
        return response()->json(null, 204);
    }
    public function indexTypeRecette()
    {
        $typeRecettes = TypeRecette::all();
        return response()->json($typeRecettes);
    }
    public function showTypeRecette(TypeRecette $typeRecette)
    {
        return response()->json($typeRecette);
    }*/
