<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Boulangerie extends Model
{
    use HasFactory;
    protected $fillable = ["nom","company_id","prix_pain_livreur","prix_pain_client","boulangerie_id"];

    public static function requireBoulangerieOfLoggedInUser(): Boulangerie
    {
        if (app()->runningUnitTests()) {
            return Boulangerie::factory()::mockActiveBoulangerie();
        }
        // TODO change this to the actual user
        return  Boulangerie::first() ?? Boulangerie::factory()->create();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }
    public function chariots(): HasMany
    {
        return $this->hasMany(Chariot::class);
    }
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function boutiques(): HasMany
    {
        return $this->hasMany(Boutique::class);
    }
    public function abonnements(): HasManyThrough
    {
        return $this->hasManyThrough(Abonnement::class, Client::class);

    }
    public function livreurs(): HasMany
    {
        return $this->hasMany(Livreur::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }
    public function typeDepenses(): HasMany
    {
        return $this->hasMany(TypeDepense::class);
    }
    public function typeRecettes(): HasMany
    {
        return $this->hasMany(TypeRecette::class);
    }
    public function recettes(): HasMany
    {
        return $this->hasMany(Recette::class);
    }



}
