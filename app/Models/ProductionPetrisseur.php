<?php
/**
 * @noinspection PhpUnused
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $date_production
 */
class ProductionPetrisseur extends Model
{
    use HasFactory;
    protected $table ="production_petrisseurs";
    protected $fillable = ["date_production","boulangerie_id","nombre_chariot","nombre_pain","nombre_plat","nombre_sac"];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }


}
