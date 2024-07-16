<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'prenom',
        'nom',
        'telephone',
    ];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);

    }
    public function compteClient(): HasOne
    {
        return $this->HasOne(CompteClient::class);
    }
    public function abonnement(): HasOne
    {
        return $this->HasOne(Abonnement::class);
    }
    public function identifier() : string
    {
        return strtoupper($this->prenom . ' ' . $this->nom . ' : ' ). $this->telephone;
    }
}
