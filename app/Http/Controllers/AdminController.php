<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\Chariot;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\CompteLivreur;
use App\Models\Depense;
use App\Models\DistribPanetier;
use App\Models\ProductionPetrisseur;
use App\Models\Recette;
use App\Models\StockIntrant;
use App\Models\TypeRecette;
use App\Models\User;
use App\Models\Versement;
use App\Traits\BelongsToCurrentCompany;
use App\Traits\BoulangerieScope;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    /**
     * @throws Exception
     */
    public function getUsersAndBoulangeries(Request $request)
    {
        $company = Company::requireCompanyOfLoggedInUser();
        $users = $company->users;
        $boulangeries = $company->boulangeries;
        return response()->json([
            'users' => $users->map(function (CompanyUser $companyUser) {
                return [
                    'id' => $companyUser->user->id,
                    'name' => $companyUser->user->name,
                    'phone_number' => $companyUser->user->phone_number,
                    'is_admin' => $companyUser->user->is_admin,
                    "boulangerie" => $companyUser->boulangerie?->nom,
                    "is_super_admin" => $companyUser->user->isSuperAdmin(),
                    "disabled" => (bool)$companyUser->user->disabled,
                ];
            }),
            'boulangeries' => $boulangeries->map(function (Boulangerie $boulangerie) {
                return [
                    'id' => $boulangerie->id,
                    'nom' => $boulangerie->nom
                ];
            }),
        ]);
    }
    public function createUser(Request $request)
    {
        if (! $request->user()->isSuperAdmin()){
            return response()->json(['message' => 'Vous n\'avez pas les droits pour effectuer cette action'], 422);
        }
        $data = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|unique:users,phone_number',
            'password' => 'required|string|min:4',
            'boulangerie_id' => 'required|exists:boulangeries,id'
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->phone_number = $data['phone_number'];
        $user->password = Hash::make($data['password']);
        $user->email = 'user'.$data['phone_number'] . '@sypain.com';
        DB::transaction(function () use ($user, $data) {
            $user->save();
            // create company user
            $companyUser = new CompanyUser();
            $companyUser->user_id = $user->id;
            $companyUser->company_id = Company::requireCompanyOfLoggedInUser()->id;
            $companyUser->boulangerie_id = $data['boulangerie_id'];
            $companyUser->save();

        });

        return response()->json($user, 201);
    }

    /**
     * @throws Exception
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|unique:users,phone_number,'.$user->id,
            'boulangerie_id' => 'exists:boulangeries,id'
        ],[
            'phone_number.unique' => 'Ce numéro de téléphone est déjà utilisé'
        ]);
        if (isset($data['boulangerie_id'])){
            // check if boulangerie belongs to current company
            $company = Company::requireCompanyOfLoggedInUser();
            if (!in_array($data['boulangerie_id'], $company->boulangeries->pluck('id')->toArray())) {
                return response()->json(['message' => 'Cette boulangerie n\'appartient pas à votre entreprise'], 403);
            }
        }
        DB::transaction(function () use ($user, $validated) {
            $user->update($validated);
            $companyUser = CompanyUser::where('user_id', $user->id)->first();
            if (isset($validated['boulangerie_id'])) {
                $companyUser->boulangerie_id = $validated['boulangerie_id'];
            }
            $user->update($validated);
            $companyUser->save();
        });


        return response()->json($user);
    }
    public function getBoulangerieData(Boulangerie $boulangerie)
    {
        $chariots = $boulangerie->chariots()->withoutGlobalScope('boulangerie')->get();
        $boulangerie->chariots = $chariots;
        return $boulangerie;


    }

    /**
     * @throws Exception
     */
    public function updateBoulangerieData(Request $request, Boulangerie $boulangerie)
    {

        if (! $boulangerie::belongsToCurrentCompany(Company::requireCompanyOfLoggedInUser(), $boulangerie)) {
            return response()->json(['message' => 'Vous n\'avez pas les droits pour effectuer cette action'], 403);
        }
        $data = $request->validate([
            'nom' => 'string',
            "prix_pain_livreur" => 'numeric',
            "prix_pain_client" => 'numeric',
            "prix_pain_boutique" => 'numeric',
            "boulangerie_id" => 'exists:boulangeries,id',
            'chariots' => 'array',

        ]);

        $boulangerie->update($data);
        if (isset($data['chariots'])) {
           foreach ($data['chariots'] as $chariot) {
               $boulangerie->chariots()->withoutGlobalScope('boulangerie')->updateOrCreate(['id' => $chariot['id']],
                   $chariot);
           }
        }
        return $boulangerie;

    }

    /**
     * @throws Exception
     */
    public function deleteChariot($chariot)
    {
        $chariot = Chariot::withoutGlobalScope('boulangerie')->findOrFail($chariot);
        if ($chariot::belongsToCurrentCompany(Company::requireCompanyOfLoggedInUser(), $chariot)) {
            $chariot->delete();
            return response()->json('OK');
        }
        return response()->json(['message'=>'Vous n\'avez pas les droits pour effectuer cette action'], 403);

    }
    public function toggleUserBanState(User $user)
    {
        if ($user->isSuperAdmin()){
            return response()->json(['message' => 'Vous ne pouvez pas désactiver un super admin'], 422);
        }
        $user->disabled = !$user->disabled;
        $user->save();
        return response()->json($user);
    }

}
