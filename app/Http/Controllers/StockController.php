<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Intrant;
use App\Models\MouveIntrant;
use Illuminate\Http\JsonResponse;
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
                   "boulangerie_id" => Boulangerie::requireBoulangerieOfLoggedInUser()->id
               ]);

               $stock->mouvements()->create([
                   "quantite" => $intrantData['quantite'],
                   "stock_avant" => 0,
                   "stock_apres" => $intrantData['quantite'],
                   "type" => "in",
                   "metadata" => $stock->toArray(),
                   'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id
               ]);

           }else {
               $stock->update([
                   "quantite" => $stock->quantite + $intrantData['quantite'],
                   "nom" => "Stock de " . $intrant->nom . ": du " . now()->format('Y-m-d H:i'),
                   "code_bar" => now()->timestamp, // generate a unique code bar (timestamp
                   "prix_achat" => $intrantData['prix_achat'],
                   "boulangerie_id" => Boulangerie::requireBoulangerieOfLoggedInUser()->id

               ]);
                $stock->mouvements()->create([
                     "quantite" => $intrantData['quantite'],
                     "stock_avant" => $stock->quantite - $intrantData['quantite'],
                     "stock_apres" => $stock->quantite,
                     "type" => "in",
                     "metadata" => $stock->toArray(),
                        'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id
                ]);
           }
        }
        return response()->json(["message"=>"Stock mis à jour"], 201);
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
    /**
     * Get movements for a specific intrant.
     *
     * @param Intrant $intrant
     * @return JsonResponse
     */
    public function getMovements(Intrant $intrant)
    {
        $movements = MouveIntrant::whereHas('stockIntrant', function($query) use ($intrant) {
            $query->where('intrant_id', $intrant->id);
        })->orderByDesc('created_at')->get()->map(function ($movement) {
            return [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantite' => $movement->quantite,
                'stock_avant' => $movement->stock_avant,
                'stock_apres' => $movement->stock_apres,
                'created_at' => $movement->created_at->toDateTimeString(),
                'metadata' => $movement->metadata,
            ];
        });

        return response()->json($movements);
    }


}
