<?php

namespace App\Http\Controllers;

use App\Http\Resources\DistribPanetierResource;
use App\Http\Resources\ProdPanetierResource;
use App\Models\Boulangerie;
use App\Models\ProductionPanetier;
use App\Rules\UniqueProductionPeriode;
use Illuminate\Http\Request;

class PanetierController extends Controller
{
    //save production panetier
    public function index()
    {
        $productions = ProductionPanetier::with('distribPanetiers')->whereBetween('date_production', [now()
                ->startOfMonth()->toDateString(), now()
            ->endOfMonth()->toDateString()])
            ->orderByDesc('date_production')
            ->get();
        return response()->json($productions);
    }

    // get production pantier du jour
    public function productionDuJour($date)
    {
        $productions = ProductionPanetier::with('distribPanetiers')->where('date_production', $date)->get();
        return response()->json($productions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date_production' => ['date_format:Y-m-d','required', new UniqueProductionPeriode($request->get('date_production'), $request->get('periode'))],
            'nombre_pain' => 'required|integer',
            'nombre_plat' => 'required|integer',
            'nombre_sac' => 'required',
            'ration' => 'required|integer',
            'donation' => 'required|integer',
            'casse' => 'required|integer',
            'chariots' => '|required|array',
            'periode' => 'required|in:matin,soir',
            'mange' => 'required|integer',
            'production_petrisseur_id' => 'required|integer|exists:production_petrisseurs,id',
        ]);
        $productionPanetier = new ProductionPanetier($data);
        $productionPanetier->boulangerie()->associate(Boulangerie::requireBoulangerieOfLoggedInUser());
        $productionPanetier->save();
        $productionPanetier->chariots()->createMany($request->chariots);
        return response()->json($productionPanetier->load('chariots'), 201);
    }


    public function update(Request $request, ProductionPanetier $productionPanetier)
    {
        $data = $request->validate([
            'date_production' => 'date',
            'nombre_pain' => 'integer',
            'nombre_plat' => 'integer',
            'nombre_sac' => 'integer',
            "nombre_chariot" => "integer",
            'ration' => 'integer',
            'donation' => 'integer',
            'casse' => 'integer',
            'mange' => 'integer',
            'chariots' => 'array',
        ]);
        $productionPanetier->update($data);
        return response()->json($productionPanetier);
    }

    public function destroy(ProductionPanetier $productionPanetier)
    {
        $productionPanetier->delete();
        return response()->json(null, 204);
    }

    public function show(ProductionPanetier $productionPanetier)
    {
        $productionPanetier->load('chariots');
        $productionPanetier->load('productionPetrisseur');

        $livreurs = $productionPanetier->distribPanetiers()->whereNotNull('livreur_id')->get();
        $clients = $productionPanetier->distribPanetiers()->whereNotNull('client_id')->get();
        $abonnements = $productionPanetier->distribPanetiers()->whereNotNull('abonnement_id')->get();
        $boutiques = $productionPanetier->distribPanetiers()->whereNotNull('boutique_id')->get();
        return response()->json([
            'productionPanetier' => new ProdPanetierResource($productionPanetier),
            'livreurs' => DistribPanetierResource::collection($livreurs),
            'clients' => DistribPanetierResource::collection($clients),
            'abonnements' => DistribPanetierResource::collection($abonnements),
            'boutiques' => DistribPanetierResource::collection($boutiques),

        ]);

    }



}
