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
            $table->unsignedTinyInteger('version')
                  ->default(0)
                  ->after('unit_penanggung_jawab_jabatan_id')
                  ->comment('0: data lama, 1: data baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_events', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
