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
        Schema::table('skala_kpmrs', function (Blueprint $table) {
            //
            $table->integer('min')->nullable()->after('deskripsi');
            $table->integer('max')->nullable()->after('min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skala_kpmrs', function (Blueprint $table) {
            //
            $table->dropColumn(['min', 'max']);
        });
    }
};
