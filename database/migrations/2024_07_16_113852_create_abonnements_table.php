<?php

use App\Models\Client;
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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->nullable(false)->constrained()->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->dateTime('date_debut')->nullable(false)->default(today()->toDateTimeString());
            $table->dateTime('date_fin')->nullable(false)->default(today()->addMonth()->toDateTimeString());
            $table->integer('solde_pain')->default(0);
            $table->integer('dette')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
