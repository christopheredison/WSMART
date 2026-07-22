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
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->dropColumn('skala_risiko_q1');
            $table->dropColumn('level_risiko_q1');
            $table->dropColumn('skala_risiko_q2');
            $table->dropColumn('level_risiko_q2');
            $table->dropColumn('skala_risiko_q3');
            $table->dropColumn('level_risiko_q3');
            $table->dropColumn('skala_risiko_q4');
            $table->dropColumn('level_risiko_q4');

            $table->json('additional_data')->nullable()->after('level_risiko_residual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->dropColumn('additional_data');

            $table->integer('skala_risiko_q1')->nullable()->after('level_risiko_residual');
            $table->string('level_risiko_q1')->nullable()->after('skala_risiko_q1');
            $table->integer('skala_risiko_q2')->nullable()->after('level_risiko_q1');
            $table->string('level_risiko_q2')->nullable()->after('skala_risiko_q2');
            $table->integer('skala_risiko_q3')->nullable()->after('level_risiko_q2');
            $table->string('level_risiko_q3')->nullable()->after('skala_risiko_q3');
            $table->integer('skala_risiko_q4')->nullable()->after('level_risiko_q3');
            $table->string('level_risiko_q4')->nullable()->after('skala_risiko_q4');
        });
    }
};
