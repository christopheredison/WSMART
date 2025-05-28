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
            // setelah kolom nilai_konversi
            $table->decimal('score_aspek_kinerja', 10, 2)
                  ->nullable()
                  ->after('nilai_konversi');

            $table->decimal('final_score_rmi', 10, 2)
                  ->nullable()
                  ->after('score_aspek_kinerja');

            $table->decimal('adjusment_score', 10, 2)
                  ->nullable()
                  ->after('final_score_rmi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            //
            $table->dropColumn([
                'score_aspek_kinerja',
                'final_score_rmi',
                'adjusment_score',
            ]);
        });
    }
};
