<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionPantierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('production_pantier', function (Blueprint $table) {
            $table->id();
            $table->date('jour');
            $table->integer('nombre_pain');
            $table->integer('nombre_chariot_21');
            $table->integer('nombre_chariot_24');
            $table->integer('nombre_plat');
            $table->integer('nombre_sac');

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
        Schema::dropIfExists('production_pantier');
    }
}
