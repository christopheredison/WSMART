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
        Schema::create('perlakuan_penyebab_risiko_unit_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perlakuan_penyebab_risiko_unit_id')->constrained('perlakuan_penyebab_risiko_units', 'id', 'fk_pprudppruid2')->onDelete('cascade');
            $table->foreignId('unit_risk_monitoring_id')->constrained('unit_risk_monitorings', 'id', 'fk_pprudppruid3')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('quarter');
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
        Schema::dropIfExists('perlakuan_penyebab_risiko_unit_documents');
    }
};
