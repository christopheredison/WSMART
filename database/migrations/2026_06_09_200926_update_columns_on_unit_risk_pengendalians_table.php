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
        Schema::table('unit_risk_pengendalians', function (Blueprint $table) {
            // Mengubah kolom yang sudah ada menjadi nullable
            $table->unsignedBigInteger('parameter_id')->nullable()->change();

            $table->unsignedBigInteger('kri_id')->nullable(); 
            $table->decimal('biaya_rencana_pengendalian', 20, 2)->nullable();
            $table->decimal('biaya_realisasi_pengendalian', 20, 2)->nullable();

            // Opsional: Menambahkan foreign key constraint
            // $table->foreign('kri_id')->references('id')->on('key_risk_indicators')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_risk_pengendalians', function (Blueprint $table) {
            $table->unsignedBigInteger('parameter_id')->nullable(false)->change();

            $table->dropColumn([
                'kri_id',
                'biaya_rencana_pengendalian',
                'biaya_realisasi_pengendalian'
            ]);
        });
    }
};