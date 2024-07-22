<?php

use App\Models\Company;
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
        Schema::create('boulangeries', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable(false);
            $table->integer('prix_pain_livreur')->nullable(false)->default(145);
            $table->integer('prix_pain_client')->nullable(false)->default(150);
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boulangeries');
    }
};
