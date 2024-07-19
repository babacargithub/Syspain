<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Intrant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntrantController extends Controller
{
    /**
     * Display a listing of the intrants.
     * @return JsonResponse
     */
    public function index()
    {
        $stocks = Intrant::whereBoulangerieId(Boulangerie::requireBoulangerieOfLoggedInUser()->id)->get();
        return response()->json($stocks->map(function (Intrant $intrant) {
            return [
                "id" => $intrant->id,
                "nom" => $intrant->nom,
                "stock" => $intrant->stock()->sum("quantite"),
            ];
        }));
    }

    /**
     * Store a newly created stock in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|unique:intrants,nom',
            // Add other fields as necessary
        ]);


        $intrant = new Intrant($validatedData);
        $intrant->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $intrant->save();
        // create stock
        $intrant->stock()->create([
            "quantite" => 0,
            "nom" => "Stock de ".$intrant->nom,
            "code_bar" => now()->timestamp, // generate a unique code bar (timestamp
            "prix_achat" => 0,
            "boulangerie_id" => $intrant->boulangerie_id
        ]);
        return response()->json($intrant, 201);
    }

    /**
     * Display the specified stock.
     *
     * @param Intrant $intrant
     * @return JsonResponse
     */
    public function show(Intrant $intrant)
    {
        return response()->json($intrant);
    }

    /**
     * Update the specified stock in storage.
     *
     * @param Request $request
     * @param Intrant $intrant
     * @return JsonResponse
     */
    public function update(Request $request,Intrant $intrant)
    {
        $validatedData = $request->validate([
            'nom' => 'string|max:255|unique:intrants,nom,'.$intrant->id,
        ]);

        $intrant->update($validatedData);
        return response()->json($intrant);
    }

    /**
     * Remove the specified stock from storage.
     *
     * @param Intrant $intrant
     * @return JsonResponse
     */
    public function destroy(Intrant $intrant)
    {
        $intrant->delete();
        return response()->json(null, 204);
    }
}