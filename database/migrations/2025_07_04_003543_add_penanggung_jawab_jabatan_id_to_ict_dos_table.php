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
        Schema::table('ict_dos', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('penanggung_jawab_jabatan_id')->nullable()->after('penanggung_jawab');
            $table->foreign('penanggung_jawab_jabatan_id')
                  ->references('id')
                  ->on('jabatans')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_dos', function (Blueprint $table) {
            //
            $table->dropForeign(['penanggung_jawab_jabatan_id']);
            $table->dropColumn('penanggung_jawab_jabatan_id');
        });
    }
};
