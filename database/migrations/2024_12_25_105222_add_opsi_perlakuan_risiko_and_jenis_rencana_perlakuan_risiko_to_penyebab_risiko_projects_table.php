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
        Schema::table('penyebab_risiko_projects', function (Blueprint $table) {
            //
            $table->integer('opsi_perlakuan_risiko')->after('timeline_perlakuan_risiko')->nullable();
            $table->integer('jenis_rencana_perlakuan_risiko')->after('opsi_perlakuan_risiko')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyebab_risiko_projects', function (Blueprint $table) {
            //
            $table->dropColumn('opsi_perlakuan_risiko');
            $table->dropColumn('jenis_rencana_perlakuan_risiko');
        });
    }
};
