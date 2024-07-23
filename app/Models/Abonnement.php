<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abonnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_debut',
        'date_fin',
        'type',
        'prix',
    ];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function identifier() :string
    {
        return 'Abonnement de '. $this->client->identifier();

    }
}
