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
        Schema::table('metrik_strategi_risikos', function (Blueprint $table) {
            //
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['peristiwa_risiko_id']);
            
            // Ubah kolom menjadi nullable dan tambahkan kembali foreign key constraint
            $table->foreignId('peristiwa_risiko_id')->nullable()->change();
            $table->foreign('peristiwa_risiko_id')->references('id')->on('peristiwa_risikos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metrik_strategi_risikos', function (Blueprint $table) {
            //
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['peristiwa_risiko_id']);
            
            // Kembalikan kolom menjadi tidak nullable dan tambahkan kembali foreign key constraint
            $table->foreignId('peristiwa_risiko_id')->nullable(false)->change();
            $table->foreign('peristiwa_risiko_id')->references('id')->on('peristiwa_risikos')->onDelete('cascade');
        });
    }
};
