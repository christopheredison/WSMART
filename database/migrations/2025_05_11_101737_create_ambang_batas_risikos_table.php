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
        Schema::create('ambang_batas_risikos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->decimal('nilai_kapasitas_risiko', 15, 2)->nullable(); // Tambahkan kolom nilai_kapasitas_risiko dengan tipe data decimal dan panjang 15, 2;
            $table->decimal('nilai_selera_risiko', 15, 2)->nullable();
            $table->decimal('nilai_toleransi_risiko', 15, 2)->nullable();
            $table->decimal('nilai_batasan_risiko', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambang_batas_risikos');
    }
};
