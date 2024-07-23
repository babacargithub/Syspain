<?php

namespace App\Traits;

use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * @method static addGlobalScope(string $string, \Closure $param)
 */
trait BoulangerieScope
{
    /**
     * Boot the BoulangerieScope trait for a model.
     *
     * @return void
     */
    protected static function bootBoulangerieScope(): void
    {
        /**
         * @var $this Model
         */
        static::addGlobalScope('boulangerie', function (Builder $builder) {
            // TODO change this later
            $builder->where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id);
//            if (auth()->check()) {
//                $builder->where('boulangerie_id', auth()->user()->boulangerie_id);
//            }
        });
    }

    /**
     * Scope a query to only include models with a specific boulangerie_id.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOfCurrentBoulangerie(Builder $query): Builder
    {
        return $query->where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id);
    }
}
