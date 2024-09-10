<?php
/**
 * @noinspection PhpUnused
 */
namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function chariots(): HasMany
    {
        return $this->hasMany(ChariotProdPetrisseur::class);
    }

    public function totalPain() : int
    {

        $chariotsNombrePainMultiplied = $this->chariots->map(function ($chariot) {
            return $chariot->nombre * $chariot->chariot->nombre_pain;
        });
        $totalPain = $chariotsNombrePainMultiplied->sum();
        // TODO check if has moitié
        if ($this->nombre_pain >10000000000000) {
            $totalPain += $this->nombre_pain;
        }
        return $totalPain;

    }
    public function getTotalPainAttribute(): int
    {
        return $this->totalPain();
    }

    public function getRendementAttribute(): float
    {
        // get total pain divided by nombre sac without floating point
        try {
            return round($this->totalPain / $this->nombre_sac);
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function getNombreChariotAttribute(): int
    {
        return $this->chariots->map(function ($chariot) {
            return $chariot->nombre;
        })->sum();
    }
    protected $appends = ['total_pain','rendement','nombre_chariot'];

    public function prodPanetier() : HasOne
    {
        return $this->hasOne(ProductionPanetier::class);

    }



}
