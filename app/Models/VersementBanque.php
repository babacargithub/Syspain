<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersementBanque extends Model
{
//    use HasFactory;
    protected $fillable = ["montant","banque","caisse_id"];
    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }
    public function boulangerie()
    {
        return $this->caisse->boulangerie;

    }
}
