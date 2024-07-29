<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\ChariotProdPetrisseur;
use App\Models\Company;
use App\Models\Intrant;
use App\Models\ProductionPetrisseur;
use Illuminate\Http\Request;

class PetrisseurController extends Controller
{
    // enregistre production petrisseur
    public function index()
    {
        $productions = ProductionPetrisseur::ofCurrentBoulangerie()->orderByDesc('date_production')->limit(30)->get();
        return response()->json($productions);
    }
    public function productionDuJour($date)
    {
        $productions = ProductionPetrisseur::ofCurrentBoulangerie()
            ->with('chariots')
            ->where('date_production', $date)->get();

        return response()->json($productions->map(function (ProductionPetrisseur $petrisseur){
            return [
                'id' => $petrisseur->id,
                'date_production' => $petrisseur->date_production,
                'nombre_chariot' => $petrisseur->nombre_chariot,
                'nombre_sac' => $petrisseur->nombre_sac,
                'nombre_plat' => $petrisseur->nombre_plat,
                'nombre_pain' => $petrisseur->nombre_pain,
                'rendement' => $petrisseur->rendement,
                'chariots' => $petrisseur->chariots->map(function (ChariotProdPetrisseur $chariot){
                    return [
                        'id' => $chariot->id,
                        'chariot_id' => $chariot->chariot_id,
                        'nombre' => $chariot->nombre,
                        'nom_chariot' => $chariot->chariot->nom,
                        'nombre_pain' => $chariot->chariot->nombre_pain,
                    ];
                })
            ];
        }));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            // date production should be unique for a boulangerie_id
            'date_production' => 'required|date:Y-m-d|unique:production_petrisseurs,date_production,NULL,id,boulangerie_id,' . Boulangerie::requireBoulangerieOfLoggedInUser()->id,
            'nombre_chariot' => 'integer',
            'nombre_sac'  => 'required|integer', // 'nombre_sac' => 'required|integer
            'nombre_plat'  => 'required|integer',
            'nombre_pain'  => 'integer',
            "rendement" => 'integer',
            "chariots" => 'required|array',
        ], [
            'date_production.unique' => 'La production de cette date a déjà été enregistrée',
        ]);
        $production = new ProductionPetrisseur($data);

        // attach chariots
        $chariots = collect($data['chariots'])->map(function ($chariot) {
            return new ChariotProdPetrisseur($chariot);
        });

        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();

        $production->boulangerie()->associate($boulangerie);
        $production->save();
        $production->chariots()->saveMany($chariots);
        // réduire stock de farine
        $intrantFarine = Intrant::where('nom', 'LIKE','%farine%')
            ->whereBoulangerieId($boulangerie->id)
            ->first();
        if ($intrantFarine != null) {
            $stockFarine = $intrantFarine->stock;
            $stockFarine->diminuerStock($production->nombre_sac);
        }
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
            'rendement'  => 'integer',
            'nombre_pain'  => 'integer',
            'chariots' => 'array',
        ]);

        $petrisseur->update($data);
       foreach ($data['chariots'] as $chariot) {
            $chariotProd = ChariotProdPetrisseur::whereChariotId($chariot['chariot_id'])->whereProductionPetrisseurId($petrisseur->id)->first();
           $chariotProd?->update($chariot);
       }
        $petrisseur->save();
        return response()->json($petrisseur);
    }
    public function destroy(ProductionPetrisseur $petrisseur)
    {
        $petrisseur->delete();
        return response()->json(null, 204);
    }

}
