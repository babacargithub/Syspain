<?php
/**
 * @noinspection PhpUnused
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouveIntrant extends Model
{
    protected $fillable  = ["stock_intrant_id", "boulangerie_id", "quantite", "stock_avant", "stock_apres", "type", "metadata"];
    use HasFactory;

    protected $casts = ["metadata" => 'array'];
}
