<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boulangerie extends Model
{
    use HasFactory;
    protected $fillable = ["nom","company_id"];

    public static function requireBoulangerieOfLoggedInUser(): Boulangerie
    {
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

    public function boutiques(): HasMany
    {
        return $this->hasMany(Boutique::class);
    }
}
