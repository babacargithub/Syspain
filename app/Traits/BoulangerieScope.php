<?php

namespace App\Traits;

use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


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
            $builder->where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id);

        });
    }

    /**
     * Scope a query to only include models with a specific boulangerie_id.
     *
     * @param Builder $query
     * @return Builder
     * @throws ContainerExceptionInterface
     */
    public function scopeOfCurrentBoulangerie(Builder $query): Builder
    {
        return $query->where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id);
    }
}
