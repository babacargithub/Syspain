<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boutique extends Model
{
    use BoulangerieScope;
    use HasFactory;
    protected $fillable = ["nom", "boulangerie_id", "solde_pain", "adresse"];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function identifier(): string
    {
        return  $this->nom;

    }
}
