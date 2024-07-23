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
