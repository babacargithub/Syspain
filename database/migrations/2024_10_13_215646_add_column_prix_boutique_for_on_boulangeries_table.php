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
        Schema::table('boulangeries', function (Blueprint $table) {

            $table->integer('prix_pain_boutique')->default(185);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->dropColumn('prix_pain_boutique');
        });
    }
};
