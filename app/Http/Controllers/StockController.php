<?php

namespace App\Http\Controllers;

use App\Models\Intrant;
use Illuminate\Http\Request;

class StockController extends Controller
{
    //store
    public function entreeStock(Request $request)
    {
        $validatedData = $request->validate([
            'intrants' => 'array|required',
            'intrants.*.intrant_id' => 'required|integer|exists:intrants,id',
            'intrants.*.quantite' => 'required|integer|min:1',
            'intrants.*.prix_achat' => 'required|numeric|min:1',
        ]);
        // for each intrant create intrant stock and update stock
        foreach ($validatedData['intrants'] as $intrantData) {
            $intrant = Intrant::findOrFail($intrantData['intrant_id']);
           $stock = $intrant->stock;
           if ($stock == null){
               $stock = $intrant->stock()->create([
                   "quantite" => $intrantData['quantite'],
                   "nom" => "Stock de " . $intrant->nom. ": du ".now()->format('Y-m-d H:i'),
                   "code_bar" => now()->timestamp, // generate a unique code bar (timestamp
                   "prix_achat" => $intrantData['prix_achat'],
                   "boulangerie_id" => $intrant->boulangerie_id
               ]);

           }else {
               $stock->update([
                   "quantite" => $intrantData['quantite'],
                   "nom" => "Stock de " . $intrant->nom . ": du " . now()->format('Y-m-d H:i'),
                   "code_bar" => now()->timestamp, // generate a unique code bar (timestamp
                   "prix_achat" => $intrantData['prix_achat'],
                   "boulangerie_id" => $intrant->boulangerie_id
               ]);
           }
        }
    }
    public function sortieStock(Intrant $intrant)
    {
        $validatedData = request()->validate([
            'quantite' => 'required|integer|min:1',
        ]);

        // find stock to update
        $stock = $intrant->stock;
        if ($stock->quantite < $validatedData['quantite']){
            return response()->json(["message"=>"Quantite insuffisante"], 422);
        }

        $stock->diminuerStock($validatedData['quantite']);
        $stock->save();

    }


}
