<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Livreur extends Model
{
    use HasFactory;
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
    public function identifier() : string
    {
        return strtoupper($this->prenom . ' ' . $this->nom . ' : ' ). $this->telephone;

    }
}
