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
        Schema::table('loss_events', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('unit_penanggung_jawab_jabatan_id')->nullable()->after('unit_penanggung_jawab');
            $table->foreign('unit_penanggung_jawab_jabatan_id')
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
        Schema::table('loss_events', function (Blueprint $table) {
            //
            $table->dropForeign(['unit_penanggung_jawab_jabatan_id']);
            $table->dropColumn('unit_penanggung_jawab_jabatan_id');
        });
    }
};
