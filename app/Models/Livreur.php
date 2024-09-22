<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Livreur extends Model
{
    use HasFactory;
    use BoulangerieScope;
    protected $fillable = [
        'prenom',
        'nom',
        'telephone',
        'boulangerie_id',
        'prix_pain',
        "is_active"
    ];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function compteLivreur(): HasOne
    {
        return $this->HasOne(CompteLivreur::class);
    }
    public function versements() : HasMany
    {
        return $this->hasMany(Versement::class);

    }
    public function distribPanetiers() : HasMany
    {
        return $this->hasMany(DistribPanetier::class);
    }
    public function identifier() : string
    {
        return strtoupper($this->prenom . ' ' . $this->nom . ' : ' ). $this->telephone;

    }

    public function getPrixPainAttribute() : int
    {
        if ($this->attributes['prix_pain'] == null || $this->attributes['prix_pain'] == 0) {
            return $this->boulangerie->prix_pain_livreur;
        }
        return $this->attributes['prix_pain'];

    }
}
