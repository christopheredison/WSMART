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
        Schema::table('perlakuan_penyebab_risiko_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('project_monitoring_id')->nullable()->change();
        });

        Schema::table('perlakuan_dampak_risiko_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('project_monitoring_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perlakuan_penyebab_risiko_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('project_monitoring_id')->nullable(false)->change();
        });

        Schema::table('perlakuan_dampak_risiko_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('project_monitoring_id')->nullable(false)->change();
        });
    }
};
