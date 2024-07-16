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
        Schema::create('intrants', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable(false);
            $table->foreignIdFor(Boulangerie::class);
            $table->unique(['nom', 'boulangerie_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intrants');
    }
};
