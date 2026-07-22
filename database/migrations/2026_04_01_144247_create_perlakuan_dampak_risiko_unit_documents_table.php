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
        Schema::create('perlakuan_dampak_risiko_unit_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perlakuan_dampak_risiko_unit_id')->onDelete('cascade');
            $table->foreignId('unit_risk_monitoring_id')->onDelete('cascade');
            $table->foreignId('user_id')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mimetype');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlakuan_dampak_risiko_unit_documents');
    }
};
