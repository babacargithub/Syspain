<?php

namespace App\Models;

use App\Traits\BoulangerieScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $solde
 */
class Caisse extends Model
{
    use BoulangerieScope;

    use HasFactory;

    protected $fillable = ["nom", "solde","boulangerie_id"
    ];

    public static function requireCaisseOfLoggedInUser(): Caisse
    {
        $boulangerie = Boulangerie::requireBoulangerieOfLoggedInUser();
        return $boulangerie->caisses()->firstOrFail();
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(CaisseTransaction::class);
    }
    public function identifier() : string
    {
        return strtoupper($this->nom). ' : '.$this->boulangerie->nom.', solde:  ' . $this->solde;
    }

    public function augmenterSolde(int $montant, array $metadata = []): self
    {
        $this->solde += $montant;
        DB::transaction(function () use ($montant, $metadata) {
            $this->save();
            $this->saveTransaction('cashin',$montant,$this->solde - $montant, $metadata);
        });

        return  $this;
    }
    public function diminuerSolde(int $montant, array $metadata = []): self
    {
        $solde_avant = (int)$this->solde;
        $this->solde -= $montant;
        DB::transaction(function () use ($montant,$solde_avant, $metadata) {
            $this->save();
            $this->saveTransaction('cashout',$montant,($solde_avant), $metadata);
        });

        return  $this;
    }

    public function getSoldeCaisseAtDateTime(\DateTime $dateTime): int
    {
        return $this->transactions()
            ->whereDate('created_at', '<=', $dateTime->format('Y-m-d'))->latest()->first()->solde_apres ?? 0;
    }
    protected function saveTransaction($type,$montant,$solde_avant,array $metadata): self
    {
        $transaction = new CaisseTransaction($metadata);
        $transaction->type = $type;
        $transaction->commentaire = $metadata['commentaire'] ?? null;
        $transaction->metadata = $metadata['metadata'] ?? [];
        $transaction->montant = $montant;
        $transaction->solde_avant = $solde_avant;
        $transaction->solde_apres = $this->solde;
        $this->transactions()->save($transaction);
        return $this;
    }
    public function recettes() : HasMany
    {
        return $this->hasMany(Recette::class);
    }
    public function depenses() : HasMany
    {
        return $this->hasMany(Depense::class);
    }
    public function versementsBanques() : HasMany
    {
        return $this->hasMany(VersementBanque::class);
    }


}
