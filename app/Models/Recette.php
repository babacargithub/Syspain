<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recette extends Model
{
    use HasFactory;
    use BoulangerieScope;

    protected $fillable = ["montant", "type_recette_id","boulangerie_id","caisse_id","commentaire"];

    public function typeRecette(): BelongsTo
    {
        return $this->belongsTo(TypeRecette::class);
    }

    public function identifier(): string
    {
        return "{$this->typeRecette->nom}";

    }
    public function getIdentifierAttribute(): string
    {
        return $this->identifier();
    }
    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    protected $appends = ["identifier"];
}
