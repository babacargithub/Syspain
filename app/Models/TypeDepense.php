<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDepense extends Model
{
    use BoulangerieScope;

    use HasFactory;
    protected $fillable = ['nom','boulangerie_id'];
    public $timestamps = false;

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
    public function boulangerie() : BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
