<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->foreignId('skala_parameter_id')->nullable()->after('skala_probabilitas_id')->constrained('skala_parameters');
        });
    }

    public function down(): void
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->dropForeign(['skala_parameter_id']);
            $table->dropColumn(['skala_parameter_id']);
        });
    }
};
