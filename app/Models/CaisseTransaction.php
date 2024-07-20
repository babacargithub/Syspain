<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaisseTransaction extends Model
{
    use HasFactory;
    protected $fillable =["caisse_id",
    "type",
    "montant",
    "commentaire",
    "user_id",
    "solde_avant",
    "solde_apres",
    "metadata"];

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected $casts = [
        'metadata' => 'array'
    ];
}
