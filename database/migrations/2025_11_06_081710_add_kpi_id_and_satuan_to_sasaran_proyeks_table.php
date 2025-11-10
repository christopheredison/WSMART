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
            $table->bigInteger('kpi_id')->nullable()->after('id');
            $table->text('satuan')->nullable()->after('kpi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            $table->dropColumn(['kpi_id', 'satuan']);
        });
    }
};
