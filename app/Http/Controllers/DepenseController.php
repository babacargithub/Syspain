<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\Depense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepenseController extends Controller
{
    /**
     * Display a listing of dépenses.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $depenses = Depense::whereHas("caisse",function (Builder $query){
            $query->where("boulangerie_id", Boulangerie::requireBoulangerieOfLoggedInUser()->id);
        })->get();
        return response()->json($depenses);
    }

    public function depensesDate($date)
    {
        return Depense::whereHas("caisse",function (Builder $query){
            $query->where("boulangerie_id", Boulangerie::requireBoulangerieOfLoggedInUser()->id);
        })->whereDate('created_at',$date)
            ->orderByDesc('created_at')->get()->map(function (Depense $depense){
            return [
                "depense"=>$depense->typeDepense->nom,
                "montant"=>$depense->montant,
                "commentaire"=>$depense->commentaire
            ];
        });

    }

    /**
     * Store a newly created dépense in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type_depense_id' => 'required|integer|exists:type_depenses,id',
            'montant' => 'required|numeric',
            "commentaire"=>"nullable|string",
            // Add other fields as necessary
        ]);

        $depense = new Depense($validatedData);
        DB::transaction(function () use ($depense) {
            $depense->caisse()->associate(Caisse::requireCaisseOfLoggedInUser());
            $depense->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
            $depense->save();
            $caisse = $depense->caisse;
            $caisse->diminuerSolde($depense->montant);
            $caisse->save();
        });

        return response()->json($depense, 201);
    }

    /**
     * Display the specified dépense.
     *
     * @param Depense $depense
     * @return JsonResponse
     */
    public function show(Depense $depense)
    {
        return response()->json($depense);
    }

    /**
     * Update the specified dépense in storage.
     *
     * @param Request $request
     * @param Depense $depense
     * @return JsonResponse
     */
    public function update(Request $request, Depense $depense)
    {
        DB::transaction(function () use ($depense,$request) {
            $previousAmount = $depense->montant;
            $validatedData = $request->validate([
                'montant' => 'numeric',
                "commentaire"=>"nullable|string",
            ]);
            $caisse = $depense->caisse;
            $shouldIncreaseSoldeCaisse = $validatedData['montant'] > $previousAmount;
            $shouldDecreaseSoldeCaisse = $validatedData['montant'] < $previousAmount;
            if ($shouldIncreaseSoldeCaisse){
                $caisse->augmenterSolde($validatedData['montant'] - $previousAmount);
            }else if ($shouldDecreaseSoldeCaisse){
                $caisse->diminuerSolde($previousAmount - $validatedData['montant']);
            }

            $depense->update($validatedData);
            $caisse->save();
        });

        return response()->json($depense);
    }

    /**
     * Remove the specified dépense from storage.
     *
     * @param Depense $depense
     * @return JsonResponse
     */
    public function destroy(Depense $depense)
    {
        DB::transaction(function () use ($depense) {
            $caisse = $depense->caisse;
            $caisse->diminuerSolde($depense->montant);
            $depense->delete();
        });


        return response()->json(null, 204);
    }


}