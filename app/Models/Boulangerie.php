<?php

namespace App\Models;

use App\Traits\BelongsToCurrentCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Boulangerie extends Model
{
    use BelongsToCurrentCompany;
    use HasFactory;
    protected $fillable = ["nom",
        "company_id",
        "prix_pain_livreur",
        "prix_pain_client",
        "prix_pain_boutique",
        "boulangerie_id"];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public static function requireBoulangerieOfLoggedInUser(): Boulangerie
    {
        if (app()->runningUnitTests()) {
            return Boulangerie::factory()::mockActiveBoulangerie();
        }
        // if it is admin user we check is there is  active boulangerie_id in session data
        $user = auth()->user();
        if ($user === null) {

            throw new \Exception('User not logged in');

        }
        $is_admin = $user->is_admin;
            $active_boulangerie_id = request()->header('ACTIVE-BOULANGERIE-ID');
        if ($is_admin) {

            if ($active_boulangerie_id) {
                return Boulangerie::findOrFail($active_boulangerie_id);
            }else{
                // get company of user
                $company = CompanyUser::where('user_id',$user->id)->firstOrFail()->company;
                return $company->boulangeries()->firstOrFail();
            }
        }else{
            // get boulangerie of user
            $boulangerie = null;
            if ($active_boulangerie_id != null) {
                $boulangerie = CompanyUser::whereBoulangerieId($active_boulangerie_id)
                    ->whereUserId($user->id)
                    ->firstOrFail()
                    ->boulangerie;
            }
            else {
                CompanyUser::where('user_id',$user->id)
                ->firstOrFail()
                ->boulangerie;
            }

            if ($boulangerie === null) {
                throw new \Exception('Require boulangerie of logged in user failed, User not assigned to a boulangerie');
            }
            return $boulangerie;

        }

    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }
    public function chariots(): HasMany
    {
        return $this->hasMany(Chariot::class);
    }
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function boutiques(): HasMany
    {
        return $this->hasMany(Boutique::class);
    }
    public function abonnements(): HasManyThrough
    {
        return $this->hasManyThrough(Abonnement::class, Client::class);

    }
    public function livreurs(): HasMany
    {
        return $this->hasMany(Livreur::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }
    public function typeDepenses(): HasMany
    {
        return $this->hasMany(TypeDepense::class);
    }
    public function typeRecettes(): HasMany
    {
        return $this->hasMany(TypeRecette::class);
    }
    public function recettes(): HasMany
    {
        return $this->hasMany(Recette::class);
    }



}
