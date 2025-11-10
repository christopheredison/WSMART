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
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            // Cek kpi_id
            if (!Schema::hasColumn('sasaran_proyeks', 'kpi_id')) {
                $table->bigInteger('kpi_id')->nullable();
            }

            // Cek satuan
            if (!Schema::hasColumn('sasaran_proyeks', 'satuan')) {
                $table->text('satuan')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            if (Schema::hasColumn('sasaran_proyeks', 'kpi_id')) {
                $table->dropColumn('kpi_id');
            }

            if (Schema::hasColumn('sasaran_proyeks', 'satuan')) {
                $table->dropColumn('satuan');
            }
        });
    }
};
