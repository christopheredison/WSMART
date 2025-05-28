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
            //
            $table->unsignedBigInteger('tck_id')->nullable()->after('peristiwa_risiko_id');
            
            // Menambahkan foreign key constraint
            $table->foreign('tck_id')
                  ->references('id')
                  ->on('tcks')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
            // Menghapus foreign key constraint
            $table->dropForeign(['tck_id']);
            
            // Menghapus kolom tck_id
            $table->dropColumn('tck_id');
        });
    }
};
