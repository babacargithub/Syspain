<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleProdPatisserie extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'prod_patisserie_id',
        'quantite',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function prodPatisserie(): BelongsTo
    {
        return $this->belongsTo(ProdPatisserie::class);
    }


}
