<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\CompteLivreur;
use App\Models\Depense;
use App\Models\DistribPanetier;
use App\Models\ProductionPetrisseur;
use App\Models\Recette;
use App\Models\StockIntrant;
use App\Models\Versement;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function boulangeries()
    {
        // TODO make this return only the boulangeries of the logged in user
        return response()->json(Boulangerie::all());
    }
    public function changeActiveBoulangerie(Request $request)
    {
        $request->validate([
            'boulangerie_id' => 'required|exists:boulangeries,id'
        ]);
        // check if boulangeries belongs to current user
        // TODO change this to the actual user

        $boulangerie = Boulangerie::findOrFail($request->boulangerie_id);
        session()->put('active_boulangerie_id', $boulangerie->id);
        return response()->json('OK');

    }
    public function dashboard(Boulangerie $boulangerie)
    {
        $date = today()->toDateString();
        $totalProduction = 0;
        // calculate total production
        $productions = ProductionPetrisseur::whereBoulangerieId($boulangerie->id)->whereDateProduction($date)
            ->get();
        foreach ($productions as $production) {
            $totalProduction += $production->total_pain;

        }
        $totals = [
            'versementsJour' => (int)$boulangerie->versements()->whereDate('created_at', today())->sum('montant_verse'),
            'totalPain' => (int) $totalProduction,
            'totalRecettes' => (int)Recette::whereBoulangerieId($boulangerie->id)->whereDate('created_at', today())->sum
            ('montant'),
            'totalDepenses' => (int) Depense::whereBoulangerieId($boulangerie->id)->whereDate('created_at', today())
                ->sum('montant'),
            'soldeDetteLivreurs' => (int) CompteLivreur::whereHas('livreur', function ($query) use ($boulangerie) {
                $query->where('boulangerie_id', $boulangerie->id);
            })->sum('dette'),
            'soldeReliquatLivreurs' => (int) CompteLivreur::whereHas('livreur', function ($query) use ($boulangerie) {
                $query->where('boulangerie_id', $boulangerie->id);
            })->sum('solde_reliquat'),
            'soldePainLivreurs' => (int) CompteLivreur::whereHas('livreur', function ($query) use ($boulangerie) {
                $query->where('boulangerie_id', $boulangerie->id);
            })->sum('solde_pain'),
//            'totalVentePatisserie' => $boulangerie->ventesPatisserie()->sum('montant'),
//            'totalVenteBoutiques' => $boulangerie->ventesBoutique()->sum('montant'),
        //TODO change later
            'totalVentePatisserie' => 0,
            'totalVenteBoutiques' => 0,
            'totalRetoursPain' => (int) Versement::whereBoulangerieId($boulangerie->id)->whereDate('created_at', today())
                ->whereNotNull('nombre_retour')->sum('nombre_retour'),
            // TODO calculate later
            "valeurStock" => (int) StockIntrant::whereBoulangerieId($boulangerie->id)
                ->sum('quantite'),

            'totalVersementsClients' => (int)$boulangerie->versements()->whereNotNull('client_id')->sum('montant_verse'),
            'totalVersementsLivreurs' => (int) $boulangerie->versements()->whereNotNull('livreur_id')->sum('montant_verse'),
        ];

        return response()->json($totals);

    }
}
