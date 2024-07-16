<?php

namespace App\Http\Controllers;

use App\Models\Livreur;
use App\Models\VersementLivreur;
use Illuminate\Http\Request;

class VersementLivreurController extends Controller
{
    public function index()
    {
        return response()->json(Livreur::where('is_active',true)->get());

    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'montant' => 'required|numeric',
            'nombre_retour' => 'required|integer',
            'nombre_pain_matin' => 'required|integer',
            'nombre_pain_soir' => 'integer',
            "date_versement"=>"required|date|date_format:Y-m-d",// 'retour' is a boolean field, so it should be
            // 'required|boolean
            'livreur_id' => 'required|integer|exists:livreurs,id',
        ]);
        $livreur = Livreur::find($data['livreur_id']);

        $versementLivreur = new VersementLivreur();
        $versementLivreur->nombre_pain_matin = $data['nombre_pain_matin'];
        $versementLivreur->nombre_pain_soir = $data['nombre_pain_soir'];
        $versementLivreur->montant_verse = $data['montant'];
        $versementLivreur->nombre_retour = $data['nombre_retour'];
        $versementLivreur->date_versement = $data['date_versement'];
        $versementLivreur->livreur()->associate($livreur);
        $versementLivreur->save();

        $compte_livreur = $livreur->compteLivreur;
        if ($data['montant'] < $compte_livreur->dette){
            $compte_livreur->solde_reliquat = ($compte_livreur->dette - $data['montant']);
            $compte_livreur->dette = 0;
        }else{
            $compte_livreur->dette = 0;
        }
        $compte_livreur->dette -= $data['montant'];

        $compte_livreur->save();
        return response()->json($compte_livreur);
    }
}
