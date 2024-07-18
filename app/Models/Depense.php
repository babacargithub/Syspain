<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_depense_id',
        'montant',
        'commentaire',
        'caisse_id'
    ];
    public function typeDepense(): BelongsTo
    {
        return $this->belongsTo(TypeDepense::class);

    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }
}
