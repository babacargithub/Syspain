<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\Depense;
use App\Models\TypeDepense;
use App\Models\TypeRecette;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    //

    public function index()
    {
        return response()->json(Caisse::requireCaisseOfLoggedInUser());
    }

    public function caisseDate($dateStart,$dateEnd = null)
    {
        // this function returns the list of depenses, recettes, versements banques, total depenses, total recettes,
        // total versements banques, solde initial at the start of day, solde final at the end of day

        $caisse = Caisse::requireCaisseOfLoggedInUser();
        $depenses = $caisse->depenses()->whereDate('created_at',$dateStart)->get();
        $recettes = $caisse->recettes()->whereDate('created_at',$dateStart)->get();
        $versementsBanques = $caisse->versementsBanques()->whereDate('created_at',$dateStart)->get();
        $totalDepenses = $depenses->sum('montant');
        $totalRecettes = $recettes->sum('montant');
        $totalVersementsBanques = $versementsBanques->sum('montant');
        // TODO find a way to track solde initial and solde final
        $soldeInitial = $caisse->solde;
        $soldeFinal = $soldeInitial + $totalRecettes - $totalDepenses - $totalVersementsBanques;
        return response()->json([
            "typeDepenses"=>TypeDepense::ofCurrentBoulangerie()->get(),
            "typeRecettes"=>TypeRecette::ofCurrentBoulangerie()->get(),
            'depenses' => $depenses->map(function (Depense $depense){
                return [
                    "id"=>$depense->id,
                    "depense"=>$depense->typeDepense->nom,
                    "montant"=>$depense->montant,
                    "commentaire"=>$depense->commentaire,
                    "created_at"=>$depense->created_at
                ];
            }),
            'recettes' => $recettes,
            'versementsBanques' => $versementsBanques,
            'totalDepenses' => $totalDepenses,
            'totalRecettes' => $totalRecettes,
            'totalVersementsBanques' => $totalVersementsBanques,
            'soldeInitial' => $soldeInitial,
            'soldeFinal' => $soldeFinal
        ]);

    }
}
