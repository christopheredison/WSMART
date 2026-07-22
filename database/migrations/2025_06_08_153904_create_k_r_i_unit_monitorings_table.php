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
        Schema::create('k_r_i_unit_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_risk_indicator_id')->constrained('key_risk_indicators', 'id', 'fk_krid')->onDelete('cascade');
            $table->foreignId('unit_risk_monitoring_id')->constrained('unit_risk_monitorings', 'id', 'fk_urmid2')->onDelete('cascade');
            $table->integer('status_kri_terkini')->nullable()->comment('1 : Aman, 2 : Waspada, 3 : Bahaya');
            $table->string('nilai_kri_terkini')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('k_r_i_unit_monitorings');
    }
};
