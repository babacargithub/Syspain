<?php
/**
 * @noinspection PhpUnused

 */
namespace App\Models;

use App\Traits\BoulangerieScope;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionPanetier extends Model
{
    use HasFactory;
    use BoulangerieScope;

    protected $fillable = [
        'date_production',
        'nombre_pain',
        'nombre_plat',
        'nombre_sac',
        "boulangerie_id",
        "ration",
        "donation",
        "casse", "mange",
        "prix_pain_client",
        "periode",
        "prix_pain_livreur",
        'ration',
        'donation',
        'casse',
        'mange',
        'periode',
        'boulangerie_id',
        'production_petrisseur_id'
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
        $prod = ProductionPetrisseur::whereId($this->production_petrisseur_id)->first();
        return $prod !== null ? $prod->totalPain : 0;

    }

    /** @noinspection UnknownColumnInspection */
    public function getNombrePainEntregistreAttribute()
    {
        $nombre_pain_plat = $this->nombre_plat;
        return $this->chariots()
                ->selectRaw('SUM(chariot_prod_panetiers.nombre * chariots.nombre_pain) as total_nombre_pain')
                ->join('chariots', 'chariots.id', '=', 'chariot_prod_panetiers.chariot_id')
                ->where('chariot_prod_panetiers.production_panetier_id', $this->id)
                ->value('total_nombre_pain')+ $nombre_pain_plat;
    }
    public  function getTotalPainDistribueAttribute()
    {
        return $this->distribPanetiers()->sum('nombre_pain')
            + $this->casse + $this->donation + $this->mange + $this->ration;
    }

    public function identifier(): string
    {
        return 'Cahier Panetier '.strtoupper($this->periode).' du ' . $this->getDateProductionAttribue();

    }

    public function getDateProductionAttribue(): string
    {
        try {
            return Carbon::parse($this->date_production)->format('d-m-Y');
        } catch (InvalidFormatException $e) {
            return $this->date_production;
        }

    }

    public function productionPetrisseur(): BelongsTo
    {
        return $this->belongsTo(ProductionPetrisseur::class);

    }
    // get nombre pain attribute from total pain petrisseur produit
    public function getNombrePainAttribute()
    {
        return $this->getTotalPainPetrisseurProduitAttribute();
    }
    protected $appends = ['nombre_pain_entregistre', 'total_pain_distribue','total_pain_petrisseur_produit'];
}
