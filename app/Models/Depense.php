<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use HasFactory;
    use BoulangerieScope;


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
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
