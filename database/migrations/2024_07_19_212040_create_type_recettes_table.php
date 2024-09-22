<?php

use App\Models\Boulangerie;
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
        Schema::create('type_recettes', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable(false);
            $table->string('constant_name')->nullable();
            $table->unique(['constant_name',"boulangerie_id"]);

            $table->foreignIdFor(Boulangerie::class)->constrained()->cascadeOnDelete();
            $table->unique(['nom', 'boulangerie_id']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_recettes');
    }
};
