<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdPatisserie extends Model
{
    use HasFactory;
    use BoulangerieScope;

    protected $fillable = [
        'date_production',
        'boulangerie_id',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(ArticleProdPatisserie::class);
    }
}
