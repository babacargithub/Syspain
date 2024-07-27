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
        'periode'
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }


    public function articles(): HasMany
    {
        return $this->hasMany(ArticleProdPatisserie::class);
    }

    public function getNombreAVerserAttribute(): int
    {
        return $this->articles->sum('nombre_verser');
    }
    public function getMontantAVerserAttribute(): int
    {
        return $this->articles->sum('montant_a_verser');
    }
    protected $appends = ['nombre_a_verser', 'montant_a_verser'];

}
