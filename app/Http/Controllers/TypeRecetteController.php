<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\TypeRecette;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TypeRecetteController extends Controller
{
    // Fetch all types of recette
    public function index()
    {
        $typesRecettes = TypeRecette::all();
        return response()->json($typesRecettes);
    }

    // Create a new type of recette

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
        ]);
        if (TypeRecette::ofCurrentBoulangerie()->where('nom', $data['nom'])->exists()) {
            return response()->json([
                'message' => 'Ce type de recette existe déjà'
            ], 422);
        }
        $typeRecette = new TypeRecette($data);
        $typeRecette->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $typeRecette->save();

        return response()->json([
            'message' => 'Type de recette créé avec succès',
            'typeRecette' => $typeRecette
        ], 201);
    }

    // Update an existing type of recette
    public function update(Request $request, TypeRecette $typeRecette)
    {
        $request->validate([
            'nom' => 'required|string|unique:type_recettes,nom,' . $typeRecette->id . '|max:255',
        ]);

        $typeRecette->update([
            'nom' => $request->nom,
        ]);

        return response()->json([
            'message' => 'Type de recette mis à jour avec succès',
            'typeRecette' => $typeRecette
        ], 200);
    }

    // Delete an existing type of recette
    public function destroy(TypeRecette $typeRecette)
    {
        $typeRecette->delete();

        return response()->json([
            'message' => 'Type de recette supprimé avec succès'
        ], 200);
    }
}
