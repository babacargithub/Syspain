<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdPatisserie extends Model
{
    use HasFactory;
    use BoulangerieScope;

    protected $fillable = [
        'date_production',
        'boulangerie_id',
        'verse',
        'restant_transfere',
        'periode'
    ];
    protected $casts = [
        'verse' => 'boolean',
        'restant_transfere' => 'boolean'
    ];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }


    public function articles(): HasMany
    {
        return $this->hasMany(ArticleProdPatisserie::class);
    }

    /** @noinspection PhpUnused */
    public function getNombreAVerserAttribute(): int
    {
        return $this->articles->sum('nombre_verser');
    }
    /** @noinspection PhpUnused */
    public function getMontantAVerserAttribute(): int
    {
        return $this->articles->sum('montant_a_verser');
    }


    protected $appends = ['nombre_a_verser', 'montant_a_verser'];

}
