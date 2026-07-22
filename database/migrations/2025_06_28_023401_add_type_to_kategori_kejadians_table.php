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
        Schema::table('kategori_kejadians', function (Blueprint $table) {
            //
            $table->integer('type')->nullable()->comment('1: Umum, 2: Proyek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_kejadians', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
};
