<?php

use App\Models\Boulangerie;
use App\Models\Caisse;
use App\Models\TypeRecette;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Boulangerie::class)->constrained()->cascadeOnDelete();
            $table->integer('montant')->nullable(false);
            $table->foreignIdFor(Caisse::class)->constrained()->cascadeOnDelete();
            $table->text('commentaire')->nullable();
            $table->foreignIdFor(TypeRecette::class)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recettes');
    }
};
