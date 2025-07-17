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
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
            $table->unsignedTinyInteger('previous_status_risiko')->nullable()->after('status_risiko');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            //
            $table->dropColumn('previous_status_risiko');
        });
    }
};
