<?php

use App\Models\Boulangerie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('production_panetiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Boulangerie::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('date_production');
            $table->integer('nombre_pain');
            $table->integer('nombre_plat');
            $table->integer('nombre_sac');
            $table->integer('ration')->default(0);
            $table->integer('donation')->default(0);
            $table->integer('casse')->default(0);
            $table->integer('mange')->default(0);
            $table->integer('prix_pain_client')->default(120);
            $table->enum('periode', ['matin', 'soir']);
            $table->unique(['boulangerie_id','date_production', 'periode']);
            $table->integer('prix_pain_livreur')->default(150);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('production_panetier');
    }
};

