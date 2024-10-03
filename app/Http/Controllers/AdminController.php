<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Company;
use App\Models\CompteLivreur;
use App\Models\Depense;
use App\Models\DistribPanetier;
use App\Models\ProductionPetrisseur;
use App\Models\Recette;
use App\Models\StockIntrant;
use App\Models\TypeRecette;
use App\Models\Versement;
use App\Traits\BoulangerieScope;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    /**
     * @throws Exception
     */
    public function boulangeries()
    {
        //  return only the boulangeries of the logged-in user
        $company =  Company::requireCompanyOfLoggedInUser();
        return response()->json($company->boulangeries);
    }
    public function changeActiveBoulangerie(Request $request)
    {
        $request->validate([
            'boulangerie_id' => 'required|exists:boulangeries,id'
        ]);
        // check if boulangeries belongs to current user
        $company = Company::requireCompanyOfLoggedInUser();
        if (!in_array($request->boulangerie_id, $company->boulangeries->pluck('id')->toArray())) {
            return response()->json(['message' => 'Cette boulangerie n\'appartient pas à votre entreprise'], 403);
        }

        $boulangerie = Boulangerie::findOrFail($request->boulangerie_id);
        session()->put('active_boulangerie_id', $boulangerie->id);
        return response()->json('OK');

    }
    public function dashboard(Boulangerie $boulangerie, $date)
    {
        $totalProduction =
            ProductionPetrisseur::withoutGlobalScope('boulangerie')->whereBoulangerieId($boulangerie->id)
                ->whereDateProduction($date)
                ->get()
            ->sum('total_pain');
$total_dette_pain = DistribPanetier::withoutGlobalScope('boulangerie')
    ->whereHas('productionPanetier', function (Builder $query) use ($boulangerie) {
        $query->withoutGlobalScope('boulangerie')
            ->where('boulangerie_id', $boulangerie->id);
    })
    ->whereDate('created_at', $date)
    ->whereNull('versement_id')
    ->sum('nombre_pain');
        $totals = [
            'versementsJour' => (int)$boulangerie->versements()
                    ->withoutGlobalScope('boulangerie')
                    ->whereDate('created_at', $date)
                    ->sum('montant_verse'),
            'totalPain' => (int) $totalProduction,
            'totalRecettes' => (int)Recette::withoutGlobalScope('boulangerie')
                ->whereBoulangerieId($boulangerie->id)
                ->whereDate('created_at', $date)
                ->sum('montant'),
            'totalDepenses' => (int) Depense::withoutGlobalScope('boulangerie')
                    ->whereBoulangerieId($boulangerie->id)
                    ->whereDate('created_at', $date)
                     ->sum('montant'),
            // we get total pain from distribution panetiers that are have no versements and multiple it by prix pain
            // livreur of the boulangerie
            'soldeDetteLivreurs' => (int) $total_dette_pain * $boulangerie->prix_pain_livreur,
            'soldeReliquatLivreurs' => (int) CompteLivreur::withoutGlobalScope('boulangerie')->whereHas('livreur', function ($query) use ($boulangerie) {
                $query->where('boulangerie_id', $boulangerie->id);
            })->sum('solde_reliquat'),
            'soldePainLivreurs' => $total_dette_pain,
//            'totalVentePatisserie' => $boulangerie->ventesPatisserie()->sum('montant'),
//            'totalVenteBoutiques' => $boulangerie->ventesBoutique()->sum('montant'),
            'totalVentePatisserie' => (int)Recette::withoutGlobalScope('boulangerie')->whereBoulangerieId
            ($boulangerie->id)
                ->whereDate('created_at', $date)
                ->whereHas('typeRecette', function ($query) use ($boulangerie) {
                    $query->whereTypeRecetteId(TypeRecette::withoutGlobalScope('boulangerie')->whereBoulangerieId($boulangerie->id)->where('constant_name', TypeRecette::VENTE_PATISSERIE)->first()?->id);
                })->sum('montant'),
            'totalVenteBoutiques' => Versement::withoutGlobalScope('boulangerie')
                ->whereBoulangerieId($boulangerie->id)
                ->whereDate('created_at', $date)
                ->whereNotNull('boutique_id')->sum('montant_verse'),
            'totalRetoursPain' => (int) Versement::withoutGlobalScope('boulangerie')->whereBoulangerieId($boulangerie->id)->whereDate('created_at', $date)
                ->whereNotNull('nombre_retour')->sum('nombre_retour'),
            //  calculate total stock using total of quantity of intrants and intrant prix_achat
            // the sum should take total quantity of intrant and multiply by prix_achat of Intrant model using the
            // relation
            "valeurStock" => (int) StockIntrant::withoutGlobalScope('boulangerie')->whereBoulangerieId($boulangerie->id)
                ->selectRaw('SUM(quantite * prix_achat) as total')
                ->where('quantite', '>', 0)
                ->value('total'),

            'totalVersementsClients' => (int)$boulangerie->versements()->withoutGlobalScope('boulangerie')
                ->whereNotNull('client_id')
                ->whereDate('created_at', $date)
                ->sum('montant_verse'),
            'totalVersementsLivreurs' => (int) $boulangerie->versements()->withoutGlobalScope('boulangerie')
                    ->whereNotNull('livreur_id')
                ->whereDate('created_at', $date)
                ->sum('montant_verse'),
        ];

        return response()->json($totals);

    }
}
