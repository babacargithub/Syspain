<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChariotProdPetrisseur extends Model
{
    use HasFactory;
    protected $fillable = [
        'chariot_id',
        'production_petrisseur_id',
        'nombre'
    ];
    public function chariot(): BelongsTo
    {
        return $this->belongsTo(Chariot::class);
    }
    public function productionPetrisseur(): BelongsTo
    {
        return $this->belongsTo(ProductionPetrisseur::class);
    }
}
