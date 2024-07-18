<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Caisse extends Model
{
    use HasFactory;

    protected $fillable = ["nom", "solde","boulangerie_id"
    ];

    public static function requireCaisseOfLoggedInUser()
    {
        // TODO implement this method
        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();
        return Caisse::firstOrCreate([
            "nom" => "Caisse Principale ",
            "solde" => 0,
            "boulangerie_id" => $boulangerie->id
        ]);
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function identifier() : string
    {
        return strtoupper($this->nom). ' : '.$this->boulangerie->nom.', solde:  ' . $this->solde;
    }

    public function augmenterSolde(int $montant): self
    {
        $this->solde += $montant;
        return  $this;
    }
    public function diminuerSolde(int $montant): self
    {
        $this->solde -= $montant;
        return  $this;
    }
}
