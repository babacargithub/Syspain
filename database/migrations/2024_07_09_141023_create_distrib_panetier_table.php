<?php

use App\Models\Abonnement;
use App\Models\Boutique;
use App\Models\Client;
use App\Models\Livreur;
use App\Models\ProductionPanetier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistribPanetierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('distrib_panetiers', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_pain');
            $table->integer('bonus')->nullable();
            $table->foreignIdFor(Livreur::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Client::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Boutique::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Abonnement::class)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table
                ->foreignIdFor(ProductionPanetier::class)
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
        Schema::dropIfExists('distrib_panetier');
    }
}
