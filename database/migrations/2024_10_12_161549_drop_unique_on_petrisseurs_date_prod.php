<?php

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
        //
        Schema::table('production_petrisseurs', function (Blueprint $table) {

            $table->dropUnique('production_petrisseurs_date_production_unique');
            $table->unique(['date_production', 'boulangerie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('production_petrisseurs', function (Blueprint $table) {
            $table->dropUnique('production_petrisseurs_date_production_boulangerie_id_unique');
            $table->unique('date_production');
        });
    }
};
