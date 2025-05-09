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
        Schema::create('risk_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('strategi_risiko_id');
            $table->integer('jenis_risiko_id');
            $table->integer('sikap_risiko')->comment('1: Konservatif, 2: Moderat, 3: Agresif');
            $table->decimal('persentase_limit');
            $table->bigInteger('nominal_limit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_limits');
    }
};
