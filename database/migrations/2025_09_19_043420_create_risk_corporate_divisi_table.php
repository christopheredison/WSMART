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
        Schema::create('risk_corporate_divisi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('identifikasi_risiko_corporate_id');
            $table->unsignedBigInteger('identifikasi_risiko_divisi_id');
            $table->timestamps();

            $table->foreign('identifikasi_risiko_corporate_id', 'fk_corporate_risk')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');

            $table->foreign('identifikasi_risiko_divisi_id', 'fk_divisi_risk')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_corporate_divisi');
    }
};