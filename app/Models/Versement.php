<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Versement extends Model
{
    use HasFactory;
//    use BoulangerieScope;

    protected $fillable = [
        'nombre_pain_matin',
        'nombre_pain_soir',
        'nombre_retour',
        'compte_data',
        'montant_verse',
        'livreur_id',
        'client_id',
        'boutique_id',
        'abonnement_id',
        'date_versement',
        'prix_unit',
        'boulangerie_id',
        'caisse_id',
    ];
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function boutique() : BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }
    public function abonnement() : BelongsTo
    {
        return $this->belongsTo(Abonnement::class);
    }
    protected $casts = [
        'compte_data' => 'array'
    ];

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

    public function identifier(): string
    {
        if ($this->isForLivreur()) {
            return $this->livreur->identifier();
        }
        if ($this->isForClient()) {
            return $this->client->identifier();
        }
        if ($this->isForBoutique()) {
            return $this->boutique->identifier();
        }
        if ($this->isForAbonnement()) {
            return $this->abonnement->identifier();
        }
        return 'Versement';
    }

}
