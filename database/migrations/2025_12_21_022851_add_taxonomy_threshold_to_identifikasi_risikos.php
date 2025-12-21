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
            $table->unsignedBigInteger('taksonomi_risiko_id')->nullable()->after('sasaran_proyek_id');
            $table->foreign('taksonomi_risiko_id')->references('id')->on('taksonomi_risikos')->onDelete('set null');

            $table->decimal('threshold_risk_limit', 20, 2)->default(0)->after('status');
            $table->decimal('threshold_risk_appetite', 20, 2)->default(0)->after('threshold_risk_limit');
            $table->decimal('threshold_risk_tolerance', 20, 2)->default(0)->after('threshold_risk_appetite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->dropForeign(['taksonomi_risiko_id']);
            $table->dropColumn([
                'taksonomi_risiko_id',
                'threshold_risk_limit',
                'threshold_risk_appetite',
                'threshold_risk_tolerance'
            ]);
        });
    }
};
