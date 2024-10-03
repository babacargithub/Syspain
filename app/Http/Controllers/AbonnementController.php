<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    //
    public function  store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id|unique:abonnements,client_id',

        ],[
            'client_id.unique' => 'Ce client a déjà un abonnement'
        ]);

        $abonnement = new Abonnement($data);
        $abonnement->client()->associate($data['client_id']);
        $abonnement->save();

        return response()->json($abonnement);


    }
}
