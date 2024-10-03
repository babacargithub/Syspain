<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoulangerieUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'boulangerie_id',
        'user_id',
    ];
    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
