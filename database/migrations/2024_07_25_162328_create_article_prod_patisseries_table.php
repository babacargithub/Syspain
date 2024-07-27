<?php

use App\Models\Article;
use App\Models\ProdPatisserie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleProdPatisseriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_prod_patisseries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(ProdPatisserie::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('quantite');
            $table->integer('retour')->default(0);
            $table->integer('restant')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_prod_patisseries');
    }
}
