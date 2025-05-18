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
        Schema::create('metrik_strategi_risikos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('kategori_risiko_id')->constrained('kategori_risikos')->onDelete('cascade');
            $table->foreignId('jenis_risiko_id')->constrained('jenis_risikos')->onDelete('cascade');
            $table->foreignId('peristiwa_risiko_id')->constrained('peristiwa_risikos')->onDelete('cascade');
            $table->text('risk_appetite_statement')->nullable();
            $table->foreignId('sikap_risiko_id')->constrained('sikap_risikos')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrik_strategi_risikos');
    }
};
