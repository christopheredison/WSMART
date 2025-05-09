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
        Schema::table('score_parameters', function (Blueprint $table) {
            $table->string('parameter_wawancara', 255)->nullable()->after('score_parameter_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('score_parameters', function (Blueprint $table) {
            $table->dropColumn('parameter_wawancara');
        });
    }
};
