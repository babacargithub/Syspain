<?php

use App\Models\Client;
use App\Models\Livreur;
use App\Models\ProductionPantier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistribPantierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('distrib_pantier', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_pain');
            $table->foreignIdFor(Livreur::class)->nullable()
                ->constrained()->onDelete('set null');
            $table->foreignIdFor(Client::class)->nullable();
            $table->integer('vente')->default(0);
            $table->integer('abonnement')->default(0);
            $table->integer('ration')->default(0);
            $table->integer('donation')->default(0);
            $table->integer('casse')->default(0);
            $table->integer('mange')->default(0);
            $table->timestamps();
            $table
                ->foreignIdFor(ProductionPantier::class)
                ->nullable(false)
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('distrib_pantier');
    }
}
