<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_risk_impact_details', function (Blueprint $table) {
            $table->string('satuan', 50)->nullable()->after('volume');
        });
    }

    public function down(): void
    {
        Schema::table('project_risk_impact_details', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};

