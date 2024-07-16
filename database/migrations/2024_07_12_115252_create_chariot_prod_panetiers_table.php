<?php

use App\Models\Chariot;
use App\Models\ProductionPanetier;
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
        Schema::create('chariot_prod_panetiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductionPanetier::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('nombre')->nullable(false);
            $table->foreignIdFor(Chariot::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chariot_prod_panetiers');
    }
};
