<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HigherOrderCollectionProxy;

/**
 * @property $nombre_retour
 */
class DistribPanetier extends Model
{
    use HasFactory;

    protected $fillable = [
        "nombre_pain",
        "livreur_id",
        "client_id",
        "boutique_id",
        "abonnement_id",
        "production_panetier_id",
        "bonus",
        'nombre_retour',
        "versement_id"
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
    public function versement(): BelongsTo
    {
        return $this->belongsTo(Versement::class);
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

    public function valeurPain(): int
    {
        if ($this->isForLivreur()) {
            $prix_pain = $this->livreur->prix_pain;
        }
        else{
            $prix_pain = Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_livreur;
        }
        return ($this->attributes['nombre_pain'] - $this->attributes['nombre_retour'])* $prix_pain;
    }
}
