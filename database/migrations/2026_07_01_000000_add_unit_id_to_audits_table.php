<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            if (! Schema::hasColumn('audits', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->index()->after('project_risk_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });
    }
};
