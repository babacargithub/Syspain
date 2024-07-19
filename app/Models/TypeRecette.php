<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeRecette extends Model
{
    use HasFactory;
    protected $fillable = ["nom","is_active"];

    public function recettes(): HasMany
    {
        return $this->hasMany(Recette::class);
    }

}
