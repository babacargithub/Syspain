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
}
