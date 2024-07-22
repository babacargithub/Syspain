<?php
/** @noinspection PhpUnused */

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\Client;
use App\Models\Livreur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class  extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_pain_matin')->default(0);
            $table->integer('nombre_pain_soir')->default(0);
            $table->integer('nombre_retour')->default(0);
            $table->integer('montant_verse')->default(0);
            $table->foreignIdFor(model: Livreur::class)->nullable()->constrained()->onDelete('set null')->cascadeOnUpdate();
            $table->foreignIdFor(model: Client::class)->nullable()->constrained()->onDelete('set null')->cascadeOnUpdate();
            $table->foreignIdFor(model: Boutique::class)->nullable()->constrained()->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreignIdFor(model: Abonnement::class)->nullable()->constrained()->onDelete('set null')
                ->cascadeOnUpdate();
            $table->date('date_versement')->nullable(false);
            $table->integer('prix_unit')->default(0);
            $table->json('compte_data')->nullable();
            $table->foreignIdFor(Boulangerie::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Caisse::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
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
};
