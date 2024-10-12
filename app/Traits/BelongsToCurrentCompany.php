<?php

namespace App\Traits;

use App\Models\Boulangerie;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;



trait BelongsToCurrentCompany
{
    // this function checks if a given model belongs to the current company
    public  static function belongsToCurrentCompany(Company $company, Model $model)
    {
        // we check if the model has a company_id  attribut
        if ($model->company_id == null && $model->boulangerie_id == null){
            return true;
        }
        // we check if the model has a company_id  attribut
        if ($model->company_id != null){
            return $model->company_id == $company->id;
        }
        // we check if the model has a boulangerie_id  attribut
        if ($model->boulangerie_id != null){
            return $model->boulangerie->company_id == $company->id;
        }
        return false;

    }
}
