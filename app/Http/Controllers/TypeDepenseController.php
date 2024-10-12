<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\TypeDepense;
use Illuminate\Http\Request;

class TypeDepenseController extends Controller
{
    // Fetch all types of dépense
    public function index()
    {
        $typesDepenses = TypeDepense::all();
        return response()->json($typesDepenses);
    }

    // Create a new type of dépense
    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'nom' => 'required|string|unique:type_depenses,nom|max:255',
        ]);

        $typeDepense = new TypeDepense($validated_data);
        $typeDepense->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $typeDepense->save();

        return response()->json([
            'message' => 'Type de dépense créé avec succès',
            'typeDepense' => $typeDepense
        ], 201);
    }

    // Update an existing type of dépense
    public function update(Request $request, TypeDepense $typeDepense)
    {
        $request->validate([
            'nom' => 'required|string|unique:type_depenses,nom,' . $typeDepense->id . '|max:255',
        ]);

        $typeDepense->update([
            'nom' => $request->nom,
        ]);

        return response()->json([
            'message' => 'Type de dépense mis à jour avec succès',
            'typeDepense' => $typeDepense
        ], 200);
    }

    // Delete an existing type of dépense
    public function destroy(TypeDepense $typeDepense)
    {
        $typeDepense->delete();

        return response()->json([
            'message' => 'Type de dépense supprimé avec succès'
        ], 200);
    }
}
