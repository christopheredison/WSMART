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
        Schema::table('pilihan_parameter_kinerjas', function (Blueprint $table) {
            //
            $table->unsignedInteger('score')
                  ->after('scale')
                  ->nullable()
                  ->default(0)
                  ->comment('Nilai score untuk analisa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilihan_parameter_kinerjas', function (Blueprint $table) {
            //
            $table->dropColumn('score');
        });
    }
};
