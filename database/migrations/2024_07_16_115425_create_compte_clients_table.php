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
        Schema::create('compte_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class);
            $table->integer('solde_pain')->default(0);
            $table->integer('dette')->default(0);
            $table->integer('solde_reliquat')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compte_clients');
    }
};
