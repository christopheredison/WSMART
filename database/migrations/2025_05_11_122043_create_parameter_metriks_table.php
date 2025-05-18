<?php

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
        Schema::create('parameter_metriks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metrik_strategi_risiko_id')->constrained('metrik_strategi_risikos')->onDelete('cascade');
            $table->text('parameter')->nullable();
            $table->text('satuan_ukuran')->nullable();
            $table->text('nilai_batasan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_metriks');
    }
};
