<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompteClient extends Model
{
    use HasFactory;
    protected $fillable = [
        'solde_pain',
        'dette',
        'solde_reliquat',
    ];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
