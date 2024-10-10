<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\VersementBanque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersementBanqueController extends Controller
{
    //
    public function index()
    {
        return response()->json(VersementBanque::whereCaisseId(Caisse::requireCaisseOfLoggedInUser()->id)->get());
    }
    public function store()
    {
        $data = request()->validate([
            "montant" => "required|integer",
            "banque" => "required|string",
        ]);
        $versement = new VersementBanque($data);
        $versement->caisse()->associate(Caisse::requireCaisseOfLoggedInUser());


        DB::transaction(function () use ($versement) {
            $versement->save();
            $versement->caisse->diminuerSolde(montant: $versement->montant, metadata: ["commentaire" => "Versement banque  ".$versement->banque]);
            $versement->caisse->save();
        });
        return response()->json($versement);

    }
    public function destroy(VersementBanque $versement)
    {
       DB::transaction(function () use ($versement) {
           $versement->caisse->augmenterSolde(montant: $versement->montant, metadata: ["commentaire" => "Suppression versement banque ".$versement->banque]);
           $versement->caisse->save();
           $versement->delete();
         });
        return response()->json($versement);
    }

}
