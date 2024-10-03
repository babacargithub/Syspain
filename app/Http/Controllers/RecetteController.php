<?php

namespace App\Http\Controllers;

use App\Http\Resources\OperationCaisseResource;
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
        $recettes = Recette::with('typeRecette')->whereCaisseId(Caisse::requireCaisseOfLoggedInUser()->id)
            ->orderByDesc('created_at')->get()
            ->map
        (function ($recette) {
            return [
                'id' => $recette->id,
                'identifier' => $recette->identifier(),
                "montant" => $recette->montant,
                'commentaire'=>$recette->commentaire,
                "created_at" => $recette->created_at,

            ];
        });
        return response()->json($recettes);
    }

    public function recettesJour($date)
    {
        $recettes = Recette::with('typeRecette')
            ->whereCaisseId(Caisse::requireCaisseOfLoggedInUser()->id)
            ->orderByDesc('created_at')->whereDate('created_at', $date)->get();
        return response()->json(OperationCaisseResource::collection($recettes));

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
            "caisse_id" => "exists:caisses,id",
            // Add other fields as necessary
        ]);
        if (!isset($validated['caisse_id'])) {
            $validated['caisse_id'] = Caisse::requireCaisseOfLoggedInUser()->id;
        }
        $recette = new Recette($validated);
        $recette->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        DB::transaction(function () use ($recette) {
            $recette->save();
            $caisse = Caisse::requireCaisseOfLoggedInUser();
            $caisse->augmenterSolde($recette->montant);
        });
        $recette->refresh();
        return response()->json($recette, 201);
    }
    // update function
    public function update(Recette $recette)
    {
        $validated = request()->validate([
            'montant' => 'numeric|min:10',
            'type_recette_id' => 'exists:type_recettes,id',
            'commentaire' => 'nullable|string',
            // Add other fields as necessary
        ]);


        // update caisse solde
        DB::transaction(function () use ($validated,$recette) {
            $diff = $validated['montant'] > $recette->montant ? $validated['montant'] - $recette->montant : $recette->montant - $validated['montant'];

            $recette->update($validated);
            $caisse = Caisse::requireCaisseOfLoggedInUser();
            // calculate the difference between the old and new montant and update caisse accordingly
            if ($validated['montant'] > $recette->montant) {
                $caisse->augmenterSolde($diff);
            } else {
                $caisse->diminuerSolde($diff);
            }
            $recette->save();
            $caisse->augmenterSolde($recette->montant);
        });
        return response()->json($recette);
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
