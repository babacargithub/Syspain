<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChariotProdPanetier extends Model
{
    use HasFactory;
    protected $table = 'chariot_prod_panetiers';
    protected $fillable = ['production_panetier_id', 'chariot_id',"nombre"];
    public function productionPanetier(): BelongsTo
    {
        return $this->belongsTo(ProductionPanetier::class);
    }
    public function chariot(): BelongsTo
    {
        return $this->belongsTo(Chariot::class);
    }
}
