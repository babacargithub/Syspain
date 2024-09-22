<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompteLivreur extends Model
{
    use HasFactory;
    protected $fillable = [
        'solde_pain',
        'dette',
        'solde_reliquat',
    ];
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }
    public function augmenterSoldePain(int $nombre_pain): self
    {
        $this->solde_pain += $nombre_pain;
        return $this;
    }
    public function diminuerSoldePain(int $nombre_pain): self
    {
        $this->solde_pain -= $nombre_pain;
        return $this;
    }
    public function augmenterDette(int $dette): self
    {
        $this->dette += $dette;
        return $this;
    }
    public function diminuerDette(int $dette): self
    {
        $this->dette -= $dette;
        return $this;
    }
    public function augmenterSoldeReliquat(int $solde_reliquat): self
    {
        $this->solde_reliquat += $solde_reliquat;
        return $this;
    }
    public function diminuerSoldeReliquat(int $solde_reliquat): self
    {
        $this->solde_reliquat -= $solde_reliquat;
        return $this;
    }
    public function getSoldePainAttribute() : int
    {
       return $this->livreur->distribPanetiers()->where('versement_id', null)->sum('nombre_pain');

    }
    public function getDetteAttribute() : int
    {
        return $this->livreur->distribPanetiers()
            ->where('versement_id', null)->sum('nombre_pain') * $this->livreur->prix_pain;
    }
}
