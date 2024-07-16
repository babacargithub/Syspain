<?php
/**
 * @noinspection PhpUnused

 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionPanetier extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_production',
        'nombre_pain',
        'nombre_plat',
        'nombre_sac',
        'ration',
        'donation',
        'casse',
        'mange',
        'boulangerie_id'
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);

    }

    /**
     * @return HasMany
     */
    public function chariots(): HasMany
    {
        return $this->hasMany(ChariotProdPanetier::class, 'production_panetier_id');

    }

    public function distribPanetiers(): HasMany
    {
        return $this->hasMany(DistribPanetier::class);
    }
    public function getTotalPainPetrisseurProduitAttribute()
    {
        return ProductionPetrisseur::whereDateProduction($this->date_production)->sum('nombre_pain');

    }
    public function getNombrePainEntregistreAttribute()
    {
        $nombre_pain_plat = 10; // TODO dynamise this
        return $this->chariots()->sum('nombre_pain')+ ($this->nombre_plat * $nombre_pain_plat);

    }
    public  function getTotalPainDistribueAttribute()
    {
        return $this->distribPanetiers()->sum('nombre_pain')
            + $this->casse + $this->donation + $this->mange + $this->ration;
    }

    protected $appends = ['nombre_pain_entregistre', 'total_pain_distribue','total_pain_petrisseur_produit'];
}
