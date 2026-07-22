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
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->integer('is_university')
                  ->nullable()   // Mengizinkan nilai null
                  ->default(0)   // Set nilai default 0
                  ->length(1)    // Panjang integer 1
                  ->after('status'); // Tambahkan setelah kolom 'risiko'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->dropColumn('is_university'); // Menghapus kolom jika rollback
        });
    }
};
