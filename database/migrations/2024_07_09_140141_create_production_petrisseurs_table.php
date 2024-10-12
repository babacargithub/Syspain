<?php

use App\Models\Boulangerie;
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
        Schema::create('production_petrisseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Boulangerie::class);
            $table->date('date_production')->nullable(false)
                ->default(today()->toDateString());
            $table->integer('nombre_sac');
            $table->integer('nombre_chariot')->default(0);
            $table->integer('nombre_plat')->default(0);
            $table->integer('nombre_pain')->default(0);
            $table->integer('rendement')->default(0);
            $table->timestamps();
            $table->unique(['boulangerie_id', 'date_production']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_petrisseurs');
    }
};
