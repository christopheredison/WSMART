<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_kris', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel rekomendasi_risikos
            $table->foreignId('rekomendasi_risiko_id')
                  ->constrained('rekomendasi_risikos')
                  ->onDelete('cascade'); // Jika rekomendasi dihapus, KRI-nya ikut terhapus

            $table->unsignedBigInteger('kri_id')->default(0)->comment('Legacy/opsional link ke master KRI');
            $table->text('kri');
            $table->string('satuan_kri')->nullable();
            $table->string('batas_aman')->nullable();
            $table->string('batas_waspada')->nullable();
            $table->string('batas_bahaya')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_kris');
    }
};
