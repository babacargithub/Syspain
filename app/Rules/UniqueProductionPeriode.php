<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ProductionPanetier;

class UniqueProductionPeriode implements ValidationRule
{
    private ?string $date_production;
    private ?string $periode;
    private ?int $excludeId;

    public function __construct($date_production, $periode, $excludeId = null)
    {
        $this->date_production = $date_production;
        $this->periode = $periode;
        $this->excludeId = $excludeId;
    }



    public function message(): string
    {
        return 'On a déjà crée un cahier pantier pour '.$this->periode.' pour la date  '.$this->date_production;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $query = ProductionPanetier::where('date_production', $this->date_production)
            ->where('periode', $this->periode);

        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }
         if($query->exists()){
            $fail($this->message());
         };
    }
}
