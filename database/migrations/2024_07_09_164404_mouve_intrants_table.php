<?php

use App\Models\Boulangerie;
use App\Models\Intrant;
use App\Models\StockIntrant;
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
        Schema::create('mouve_intrants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StockIntrant::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();;
            $table->foreignIdFor(Boulangerie::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('quantite')->default(0);
            $table->integer('stock_avant');
            $table->integer('stock_apres');
            $table->enum('type', ['in', 'out']);
            $table->json('metadata')->nullable();
            $table->foreignIdFor(User::class)->nullable();
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
