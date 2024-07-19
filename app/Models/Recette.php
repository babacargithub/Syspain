<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recette extends Model
{
    use HasFactory;
    protected $fillable = ["montant", "type_recette_id","boulangerie_id","caisse_id","commentaire"];

    public function typeRecette(): BelongsTo
    {
        return $this->belongsTo(TypeRecette::class);
    }

    public function identifier(): string
    {
        return "Recette : {$this->typeRecette->nom}";

    }
    public function getIdentifierAttribute(): string
    {
        return $this->identifier();
    }
    protected $appends = ["identifier"];
}
