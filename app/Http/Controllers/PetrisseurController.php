<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Company;
use App\Models\Intrant;
use App\Models\ProductionPetrisseur;
use Illuminate\Http\Request;

class PetrisseurController extends Controller
{
    // enregistre production petrisseur
    public function index()
    {
        $productions = ProductionPetrisseur::all();
        return response()->json($productions);
    }
    public function productionDuJour($date)
    {
        $productions = ProductionPetrisseur::where('date_production', $date)->get();
        return response()->json($productions);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'date_production' => 'required|date',
            'nombre_chariot' => 'required|integer',
            'nombre_sac'  => 'required|integer', // 'nombre_sac' => 'required|integer
            'nombre_plat'  => 'required|integer',
            'nombre_pain'  => 'required|integer',
        ]);
        $production = new ProductionPetrisseur($data);

        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();

        $production->boulangerie()->associate($boulangerie);
        $production->save();
        // réduire stock de farine
        $intrantFarine = Intrant::where('nom', 'LIKE','%farine%')
            ->whereBoulangerieId($boulangerie->id)
            ->first();
        $stockFarine =$intrantFarine->stock;
        $stockFarine->diminuerStock($production->nombre_sac);
        return response()->json($production, 201);

    }
    public function show(ProductionPetrisseur $petrisseur)
    {

        return response()->json($petrisseur);
    }
    public function update(Request $request, ProductionPetrisseur $petrisseur)
    {
        $data = $request->validate([
            'date_production' => 'date',
            'nombre_chariot' => 'integer',
            'nombre_sac'  => 'integer',
            'nombre_plat'  => 'integer',
            'nombre_pain'  => 'integer',
        ]);

        $petrisseur->update($data);
        return response()->json($petrisseur);
    }
    public function destroy(ProductionPetrisseur $petrisseur)
    {
        $petrisseur->delete();
        return response()->json(null, 204);
    }

}
