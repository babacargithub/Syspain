<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boutique extends Model
{
    use HasFactory;
    protected $fillable = ["nom", "boulangerie_id", "solde_pain", "adresse"];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
