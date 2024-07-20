<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDepense extends Model
{
    use HasFactory;
    protected $fillable = ['nom'];
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
