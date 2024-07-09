<?php
/** @noinspection PhpUnused */

use App\Models\Livreur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCahierLivreursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('cahier_livreurs', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_pain_matin')->default(0);
            $table->integer('nombre_pain_soir')->default(0);
            $table->integer('retour')->default(0);
            $table->integer('verse')->default(0);
            $table->foreignIdFor(Livreur::class);
            $table->date('date_op')->nullable(false);
            $table->integer('prix_unit')->default(0);
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
        Schema::dropIfExists('cahier_livreurs');
    }
}
