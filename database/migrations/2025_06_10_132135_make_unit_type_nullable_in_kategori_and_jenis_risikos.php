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
        // Pastikan package doctrine/dbal sudah terpasang untuk menggunakan change()
        Schema::table('kategori_risikos', function (Blueprint $table) {
            // ubah unit_type_id menjadi nullable
            $table->unsignedBigInteger('unit_type_id')->nullable()->change();
        });

        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_type_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_risikos', function (Blueprint $table) {
            // kembalikan menjadi NOT NULL
            $table->unsignedBigInteger('unit_type_id')->nullable(false)->change();
        });

        Schema::table('jenis_risikos', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_type_id')->nullable(false)->change();
        });
    }
};
