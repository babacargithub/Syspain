<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeRecette extends Model
{
    use BoulangerieScope;

    use HasFactory;

    const VERSEMENT_LIVREUR = "versement_livreur";
    const VENTE_PATISSERIE = "vente_patisserie";
    const VERSEMENT_CLIENT = 'versement_client';
    const VERSEMENT_BOUTIQUE = 'versement_boutique';
    const VERSEMENT_ABONNEMENT = 'versement_abonnement';
    const VENTE_RESTANT = 'vente_restant';
    protected $fillable = ["nom","is_active",'boulangerie_id'];

    public function recettes(): HasMany
    {
        return $this->hasMany(Recette::class);
    }
   public function boulangerie(): BelongsTo
   {
         return $this->belongsTo(Boulangerie::class);

   }

}
