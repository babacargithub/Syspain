<?php
/**
 * @noinspection PhpUnused
 */
namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $date_production
 */
class ProductionPetrisseur extends Model
{
    use BoulangerieScope;
    use HasFactory;
    protected $table ="production_petrisseurs";
    protected $fillable = ["date_production","boulangerie_id","nombre_chariot","nombre_pain","nombre_plat","nombre_sac","rendement"];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function totalPain() : int
    {
        // TODO check again later
        return ($this->rendement * $this->nombre_sac);

    }
    public function getTotalPainAttribute(): int
    {
        return $this->totalPain();
    }

    protected $appends = ['total_pain'];



}
