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
        Schema::table('rmi_periods', function (Blueprint $table) {
            //
            $table->string('kinerja')->nullable()->after('score_rmi_desc');
            $table->string('kpmr')->nullable()->after('kinerja');
            $table->integer('peringkat_komposit_risiko')->nullable()->after('kpmr');
            $table->integer('nilai_konversi')->nullable()->after('peringkat_komposit_risiko');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            //
            $table->dropColumn(['kinerja', 'kpmr', 'peringkat_komposit_risiko', 'nilai_konversi']);
        });
    }
};
