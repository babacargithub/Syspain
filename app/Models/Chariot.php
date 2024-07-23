<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chariot extends Model
{
    use BoulangerieScope;

    use HasFactory;
    protected $fillable = ["nom","nombre_pain","boulangerie_id"];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function identifier(): string
    {
        return "Chariot ".$this->nombre_pain." pains";
    }
}
