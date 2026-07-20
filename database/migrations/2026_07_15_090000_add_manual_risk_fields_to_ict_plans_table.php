<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ict_plans', function (Blueprint $table) {
            $table->text('peristiwa_risiko')->nullable()->after('risiko_id');
            $table->text('lokasi_risiko')->nullable()->after('peristiwa_risiko');
        });
    }

    public function down(): void
    {
        Schema::table('ict_plans', function (Blueprint $table) {
            $table->dropColumn(['peristiwa_risiko', 'lokasi_risiko']);
        });
    }
};
