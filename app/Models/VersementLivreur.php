<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersementLivreur extends Model
{
    use HasFactory;
    protected $fillable = [
        'montant',
    ];
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }
}
