<?php

use App\Models\Caisse;
use App\Models\TypeDepense;
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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TypeDepense::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Caisse::class)->nullable(false)->constrained()->cascadeOnDelete();
            $table->integer('montant')->nullable(false);
            $table->string('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
