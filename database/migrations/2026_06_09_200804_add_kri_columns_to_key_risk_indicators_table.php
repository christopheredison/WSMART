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
        Schema::table('key_risk_indicators', function (Blueprint $table) {
            $table->string('tren_parameter')->nullable()->comment('Higher is Better atau Lower is Better');
            $table->text('metode_pengukuran')->nullable();
            // $table->decimal('risk_limit', 20, 2)->nullable();
            // $table->decimal('risk_appetite', 20, 2)->nullable();
            // $table->decimal('risk_tolerance', 20, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_risk_indicators', function (Blueprint $table) {
            $table->dropColumn([
                'tren_parameter',
                'metode_pengukuran',
                // 'risk_limit',
                // 'risk_appetite',
                // 'risk_tolerance'
            ]);
        });
    }
};