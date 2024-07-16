<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribPanetier extends Model
{
    use HasFactory;

    protected $fillable = [
        "nombre_pain", "livreur_id", "client_id", "boutique_id", "abonnement_id", "production_panetier_id",
    ];
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);

    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }
    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class);
    }
    public function productionPanetier(): BelongsTo
    {
        return $this->belongsTo(ProductionPanetier::class);
    }

    public function isForLivreur(): bool
    {
        return $this->livreur_id !== null;
    }
    public function isForClient(): bool
    {
        return $this->client_id !== null;
    }
    public function isForBoutique(): bool
    {
        return $this->boutique_id !== null;
    }

    public function isForAbonnement(): bool
    {
        return $this->abonnement_id !== null;
    }
}
