<?php

use App\Models\Caisse;
use App\Models\User;
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
        Schema::create('caisse_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Caisse::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('type', ['cashin', 'cashout']);
            $table->integer('montant')->nullable(false);
            $table->string('commentaire')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('solde_avant')->nullable(false);
            $table->integer('solde_apres')->nullable(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisse_transactions');
    }
};
