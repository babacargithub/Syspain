<?php

use App\Models\Caisse;
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
        Schema::create('versement_banques', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('montant')->nullable(false);
            $table->string('banque')->nullable(false);
            $table->foreignIdFor(Caisse::class)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versement_banques');
    }
};
