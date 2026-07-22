<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            if (! Schema::hasColumn('audits', 'project_risk_id')) {
                $table->unsignedBigInteger('project_risk_id')->nullable()->index()->after('identifikasi_risiko_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn('project_risk_id');
        });
    }
};
