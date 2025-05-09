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
            $table->string('timeline_perlakuan_risiko')->after('pic')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline_perlakuan_risiko', function (Blueprint $table) {
            //
            $table->dropColumn('skala_dampak');
        });
    }
};
