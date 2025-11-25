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
        // 1. Untuk Loss Event Project
        Schema::table('loss_event_projects', function (Blueprint $table) {
            $table->unsignedBigInteger('project_risk_id')->nullable()->after('project_id');
        });

        // 2. Untuk Loss Event Unit (Divisi)
        Schema::table('loss_events', function (Blueprint $table) {
            $table->unsignedBigInteger('risiko_id')->nullable()->after('unit_id');
        });

        // 3. Untuk Loss Event AP (Anak Perusahaan)
        Schema::table('loss_event_aps', function (Blueprint $table) {
            $table->unsignedBigInteger('risiko_id')->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_event_projects', function (Blueprint $table) {
            $table->dropColumn('project_risk_id');
        });
        Schema::table('loss_events', function (Blueprint $table) {
            $table->dropColumn('risiko_id');
        });
        Schema::table('loss_event_aps', function (Blueprint $table) {
            $table->dropColumn('risiko_id');
        });
    }
};
