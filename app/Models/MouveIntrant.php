<?php
/**
 * @noinspection PhpUnused
 */

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouveIntrant extends Model
{
    use BoulangerieScope;

    protected $fillable  = ["stock_intrant_id", "boulangerie_id", "quantite", "stock_avant", "stock_apres", "type", "metadata"];
    use HasFactory;

    public function stockIntrant(): BelongsTo
    {
        return $this->belongsTo(StockIntrant::class);
    }
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    protected $casts = ["metadata" => 'array'];
}
