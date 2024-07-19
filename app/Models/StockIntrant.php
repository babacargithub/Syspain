<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockIntrant extends Model
{
    use HasFactory;
    protected $fillable = ["nom","intrant_id", "boulangerie_id", "quantite", "prix_achat", "code_bar"];

    public function intrant(): BelongsTo
    {
        return $this->belongsTo(Intrant::class);
    }
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function augmenterStock(int $quantite): self
    {
        $stock_avant = $this->quantite;
        $this->quantite += $quantite;

        DB::transaction(function () use ($quantite,$stock_avant) {
            $this->save();
            $this->saveMouvement('in', $quantite, $stock_avant);
        });
        return $this;
    }
    public function mouvements() : HasMany
    {
        return $this->hasMany(MouveIntrant::class);

    }
    public function diminuerStock(int $quantite): self
    {
        $stock_avant = $this->quantite;
        $this->quantite -= $quantite;
        DB::transaction(function () use ($quantite,$stock_avant) {
            $this->save();
            $this->saveMouvement('out', $quantite, $stock_avant);
        });
        return $this;
    }
    public function identifier() : string
    {
        return strtoupper($this->intrant->nom). ' : '.$this->quantite;
    }
    // save mouvement intrant
    public function saveMouvement(string $type, int $quantite, int $stock_avant, array $metadata = []): self
    {
        $this->mouvements()->create([
            "type" => $type,
            "quantite" => $quantite,
            "stock_avant" => $stock_avant,
            "stock_apres" => $this->quantite,
            "metadata" => $metadata,
            "boulangerie_id"=>$this->boulangerie_id
        ]);
        return $this;
    }
}
