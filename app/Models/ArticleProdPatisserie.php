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
        'retour',
        'restant',
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

    public function nombreVerser(): int
    {
        return ($this->quantite - $this->restant - $this->retour);
    }
    public function montantAVerser(): int
    {
        return ($this->quantite - $this->restant - $this->retour) * $this->article->prix;
    }
    public function getNombreVerserAttribute(): int
    {
        return $this->nombreVerser();
    }
    public function getMontantAVerserAttribute(): int
    {
        return $this->montantAVerser();
    }
    protected $appends = ['nombre_verser', 'montant_a_verser'];


}
