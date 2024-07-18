<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
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
        (true)->get();
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
}