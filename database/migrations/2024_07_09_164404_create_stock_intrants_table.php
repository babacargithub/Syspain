<?php

use App\Models\Boulangerie;
use App\Models\Intrant;
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
        Schema::create('stock_intrants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Intrant::class);
            $table->foreignIdFor(Boulangerie::class);

            $table->integer('quantite')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_intrants');
    }
};
