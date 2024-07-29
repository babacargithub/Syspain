<?php

use App\Models\Boulangerie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdPatisseriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {/**/
        Schema::create('prod_patisseries', function (Blueprint $table) {
            $table->id();
            $table->date('date_production');
            $table->enum('periode', ['matin', 'soir']);
            $table->foreignIdFor(Boulangerie::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unique(['date_production','periode', 'boulangerie_id']);
            $table->boolean('verse')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prod_patisseries');
    }
}
